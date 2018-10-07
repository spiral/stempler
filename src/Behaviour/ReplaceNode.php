<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Behaviour;

use Spiral\Stempler\BehaviourInterface;
use Spiral\Stempler\CompilerInterface;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\Node;

/**
 * Replaces specified block (including tag) with external node, automatically uses inner tag
 * content as "context" block and all other tag attributes as additional node child.
 */
final class ReplaceNode implements BehaviourInterface
{
    // all inner block context goes inside this element
    private const CONTEXT_BLOCK = 'context';

    /** @var CompilerInterface */
    private $compiler ;

    /**
     * External node path.
     *
     * @var string
     */
    private $path = '';

    /**
     * Import context includes everything between opening and closing tag.
     *
     * @var array
     */
    private $context = [];

    /**
     * Context token.
     *
     * @var array
     */
    private $token = [];

    /**
     * @param CompilerInterface $compiler
     * @param string            $path
     * @param array             $context
     * @param array             $token
     */
    public function __construct(
        CompilerInterface $compiler,
        string $path,
        array $context,
        array $token = []
    ) {
        $this->compiler = $compiler;
        $this->path = $path;

        $this->context = $context;
        $this->token = $token;
    }

    /**
     * Create node to be injected into template at place of tag caused import.
     *
     * @return Node
     */
    public function createNode(): Node
    {
        //Content of node to be imported
        $node = $this->compiler->createNode($this->path, $this->token);

        //Let's register user defined blocks (context and attributes) as placeholders
        $node->mountBlock(
            self::CONTEXT_BLOCK,
            [],
            [$this->createPlaceholder(self::CONTEXT_BLOCK, $contextID)],
            true
        );

        foreach ($this->token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value) {
            //Attributes counted as blocks to replace elements in included node
            $node->mountBlock($attribute, [], [$value], true);
        }

        //We now have to compile node content to pass it's body to parent node
        $content = $node->compile($dynamic);

        //Outer blocks (usually user attributes) can be exported to template using non default
        //rendering technique, for example every "extra" attribute can be passed to specific
        //template location. Stempler to decide.
        foreach ($this->compiler->getExports() as $exporter) {
            /** @var array $dynamic */
            $content = $exporter->mountBlocks($content, $dynamic);
        }

        //Let's parse complied content without any imports (to prevent collision)
        $compiler = clone $this->compiler;

        //Outer content must be protected using unique names
        $rebuilt = new Node($compiler, $compiler->generateID(), $content);

        if (!empty($contextBlock = $rebuilt->findNode($contextID))) {
            //Now we can mount our content block
            $contextBlock->mountNode($this->contextNode());
        }

        return $rebuilt;
    }

    /**
     * Pack node context (everything between open and close tag).
     *
     * @return Node
     */
    protected function contextNode(): Node
    {
        $context = '';
        foreach ($this->context as $token) {
            $context .= $token[HtmlTokenizer::TOKEN_CONTENT];
        }

        return new Node($this->compiler, $this->compiler->generateID(), $context);
    }

    /**
     * Create placeholder block (to be injected with inner blocks defined in context).
     *
     * @param string $name
     * @param string|null $blockID
     *
     * @return string
     */
    protected function createPlaceholder(string $name, string &$blockID = null): string
    {
        $blockID = $name . '-' . $this->compiler->generateID();

        //Short block declaration syntax (match with syntax)?
        return '${' . $blockID . '}';
    }
}