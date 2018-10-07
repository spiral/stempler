<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Behaviour\ExtendLayout;
use Spiral\Stempler\Behaviour\IncludeBlock;
use Spiral\Stempler\Behaviour\InnerBlock;
use Spiral\Stempler\Exception\LoaderExceptionInterface;
use Spiral\Stempler\Exception\StemplerException;
use Spiral\Stempler\Importer\Stopper;

/**
 * Supervisors used to control node behaviours and syntax.
 */
class Supervisor implements SupervisorInterface
{
    /**
     * Used to create unique node names when required.
     *
     * @var int
     */
    private static $index = 0;

    /**
     * Active set of imports.
     *
     * @var ImporterInterface[]
     */
    private $importers = [];

    /**
     * @var SyntaxInterface
     */
    protected $syntax = null;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @param LoaderInterface $loader
     * @param SyntaxInterface $syntax
     */
    public function __construct(LoaderInterface $loader, SyntaxInterface $syntax)
    {
        $this->loader = $loader;
        $this->syntax = $syntax;
    }

    /**
     * {@inheritdoc}
     */
    public function getSyntax(): SyntaxInterface
    {
        return $this->syntax;
    }

    /**
     * Add new elements import locator.
     *
     * @param ImporterInterface $import
     */
    public function registerImporter(ImporterInterface $import)
    {
        array_unshift($this->importers, $import);
    }

    /**
     * Active templater imports.
     *
     * @return ImporterInterface[]
     */
    public function getImporters()
    {
        return $this->importers;
    }

    /**
     * Remove all element importers.
     */
    public function flushImporters()
    {
        $this->importers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function tokenBehaviour(array $token, array $content, Node $node)
    {
        switch ($this->syntax->tokenType($token, $name)) {
            case SyntaxInterface::TYPE_BLOCK:
                //Tag declares block (section)
                return new InnerBlock($name);

            case SyntaxInterface::TYPE_EXTENDS:
                //Declares parent extending
                $extends = new ExtendLayout(
                    $this->createNode($this->syntax->resolvePath($token), $token),
                    $token
                );

                //We have to combine parent imports with local one (this is why uses has to be defined
                //after extends tag!)
                $this->importers = $extends->parentImports();

                //Sending command to extend parent
                return $extends;

            case SyntaxInterface::TYPE_IMPORTER:
                //Implementation specific
                $this->registerImporter($this->syntax->createImporter($token, $this));

                //No need to include import tag into source
                return BehaviourInterface::SKIP_TOKEN;
        }

        //We now have to decide if element points to external view (source) to be imported
        foreach ($this->importers as $importer) {
            if ($importer->importable($name, $token)) {
                if ($importer instanceof Stopper) {
                    //Native importer tells us to treat this element as simple html
                    break;
                }

                //Let's include!
                return new IncludeBlock(
                    $this, $importer->resolvePath($name, $token), $content, $token
                );
            }
        }

        return BehaviourInterface::SIMPLE_TAG;
    }

    /**
     * Create node based on given location with identical supervisor (cloned).
     *
     * @param string $path
     * @param array  $token Context token.
     *
     * @return Node
     * @throws StemplerException
     */
    public function createNode(string $path, array $token = []): Node
    {
        //We support dots!
        if (!empty($token)) {
            $path = str_replace('.', '/', $path);
        }

        try {
            $context = $this->loader->getSource($path);
        } catch (LoaderExceptionInterface $e) {
            throw new StemplerException($e->getMessage(), $token, 0, $e);
        }

        try {
            //In isolation
            return new Node(clone $this, $this->uniquePlaceholder(), $context->getCode());
        } catch (StemplerException $e) {
            //Wrapping to clarify location of error
            throw $this->clarifyException($context, $e);
        }
    }

    /**
     * Get unique placeholder name, unique names are required in some cases to correctly process
     * includes and etc.
     *
     * @return string
     */
    public function uniquePlaceholder(): string
    {
        return md5(self::$index++);
    }

    /**
     * Clarify exeption with it's actual location.
     *
     * @param StemplerSource    $sourceContext
     * @param StemplerException $exception
     *
     * @return StemplerException
     */
    protected function clarifyException(
        StemplerSource $sourceContext,
        StemplerException $exception
    ) {
        if (empty($exception->getToken())) {
            //Unable to locate
            return $exception;
        }

        //We will need only first tag line
        $target = explode("\n", $exception->getToken()[HtmlTokenizer::TOKEN_CONTENT])[0];

        //Let's try to locate place where exception was used
        $lines = explode("\n", $sourceContext->getCode());

        foreach ($lines as $number => $line) {
            if (strpos($line, $target) !== false) {
                //We found where token were used (!!)
                $exception->setLocation(
                    $sourceContext->getFilename(),
                    $number + 1
                );

                break;
            }
        }

        return $exception;
    }
}