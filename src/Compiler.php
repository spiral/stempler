<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Behaviour\CreateNode;
use Spiral\Stempler\Behaviour\ExtendParent;
use Spiral\Stempler\Behaviour\ReplaceNode;
use Spiral\Stempler\Exception\CompileException;
use Spiral\Stempler\Export\AttributeExport;
use Spiral\Stempler\Import\Stop;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewSource;

/**
 * Supervisors used to control node behaviours and syntax.
 */
class Compiler implements CompilerInterface
{
    /**
     * Used to create unique node names when required.
     *
     * @var int
     */
    private static $index = 0;

    /** @var ImportInterface[] */
    private $imports = [];

    /** @var SyntaxInterface */
    protected $syntax = null;

    /** @var LoaderInterface */
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
     * Get unique placeholder name, unique names are required in some cases to correctly process
     * includes and etc.
     *
     * @return string
     */
    public function generateID(): string
    {
        return md5(self::$index++);
    }

    /**
     * {@inheritdoc}
     */
    public function getSyntax(): SyntaxInterface
    {
        return $this->syntax;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $path): string
    {
        return $this->createNode($path)->compile();
    }

    /**
     * {@inheritdoc}
     */
    public function compileString(string $source): string
    {
        $node = new Node($this, 'root', $source);

        return $node->compile();
    }

    /**
     * {@inheritdoc}
     */
    public function createNode(string $path, array $token = []): Node
    {
        //We support dots!
        if (!empty($token)) {
            $path = str_replace('.', '/', $path);
        }


        $context = $this->loader->load($path);


        try {
            //In isolation
            return new Node(clone $this, $this->generateID(), $context->getCode());
        } catch (CompileException $e) {
            //Wrapping to clarify location of error
            throw $this->clarifyException($context, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * {@inheritdoc}
     */
    public function getExports(): array
    {
        return [new AttributeExport()];
    }

    /**
     * {@inheritdoc}
     */
    public function defineToken(array $token, array $content, Node $node)
    {
        switch ($this->syntax->tokenType($token, $name)) {
            case SyntaxInterface::TYPE_BLOCK:
                //Tag declares block (section)
                return new CreateNode($name);

            case SyntaxInterface::TYPE_EXTENDS:
                //Declares parent extending
                $extends = new ExtendParent(
                    $this->createNode($this->syntax->fetchPath($token), $token),
                    $token
                );

                //We have to combine parent imports with local one (this is why uses has to be defined
                //after extends tag!)
                $this->imports = $extends->getNode()->getCompiler()->getImports();

                //Sending command to extend parent
                return $extends;

            case SyntaxInterface::TYPE_IMPORTER:
                //Implementation specific
                $this->addImport($this->syntax->createImport($token, $this));

                //No need to include import tag into source
                return BehaviourInterface::SKIP_TOKEN;
        }

        //We now have to decide if element points to external view (source) to be imported
        foreach ($this->imports as $import) {
            if ($import->importable($name, $token)) {
                if ($import instanceof Stop) {
                    //Native import tells us to treat this element as simple html
                    break;
                }

                //Let's include!
                return new ReplaceNode(
                    $this,
                    $import->resolvePath($name, $token),
                    $content,
                    $token
                );
            }
        }

        return BehaviourInterface::SIMPLE_TAG;
    }

    /**
     * Reset all imports in copied compiler.
     */
    public function __clone()
    {
        $this->imports = [];
    }

    /**
     * Register new view import.
     *
     * @param ImportInterface $import
     */
    protected function addImport(ImportInterface $import)
    {
        array_unshift($this->imports, $import);
    }

    /**
     * Clarify exception with it's actual location.
     *
     * @param ViewSource       $source
     * @param CompileException $exception
     *
     * @return CompileException
     */
    protected function clarifyException(
        ViewSource $source,
        CompileException $exception
    ): CompileException {
        if (empty($exception->getToken())) {
            //Unable to locate
            return $exception;
        }

        //We will need only first tag line
        $target = explode("\n", $exception->getToken()[HtmlTokenizer::TOKEN_CONTENT])[0];

        //Let's try to locate place where exception was used
        $lines = explode("\n", $source->getCode());

        foreach ($lines as $number => $line) {
            if (strpos($line, $target) !== false) {
                //We found where token were used (!!)
                $exception->setLocation(
                    $source->getFilename(),
                    $number + 1
                );

                break;
            }
        }

        return $exception;
    }
}