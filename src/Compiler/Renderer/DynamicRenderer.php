<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Compiler\Renderer;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Directive\DirectiveInterface;
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Node\NodeInterface;

final class DynamicRenderer implements Compiler\RendererInterface
{
    // default output filter
    public const DEFAULT_FILTER = "htmlspecialchars(%s, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8')";

    /** @var string */
    private $defaultFilter = '';

    /** @var DirectiveInterface[] */
    private $directives = [];

    /**
     * DynamicRenderer constructor.
     *
     * @param string $defaultFilter
     */
    public function __construct(string $defaultFilter = self::DEFAULT_FILTER)
    {
        $this->defaultFilter = $defaultFilter;
    }

    /**
     * Add new directive(s) compiler.
     *
     * @param DirectiveInterface $directiveCompiler
     */
    public function addDirective(DirectiveInterface $directiveCompiler)
    {
        $this->directives[] = $directiveCompiler;
    }

    /**
     * @inheritDoc
     */
    public function render(Compiler $compiler, Compiler\Result $result, NodeInterface $node): bool
    {
        switch (true) {
            case $node instanceof Output:
                $this->output($result, $node);
                return true;
            case $node instanceof Directive:
                $this->directive($result, $node);
                return true;
            default:
                return false;
        }
    }

    /**
     * @param Compiler\Result $source
     * @param Directive       $directive
     *
     * @throws DirectiveException
     */
    private function directive(Compiler\Result $source, Directive $directive)
    {
        foreach ($this->directives as $renderer) {
            $result = $renderer->render($directive);
            if ($result !== null) {
                $source->push($result, $directive->getContext());
                return;
            }
        }

        throw new DirectiveException(
            "Undefined directive `{$directive->name}`",
            $directive->getContext()
        );
    }

    /**
     * @param Compiler\Result $source
     * @param Output          $output
     */
    private function output(Compiler\Result $source, Output $output)
    {
        if ($output->rawOutput) {
            $source->push(sprintf("<?php echo %s; ?>", trim($output->body)), $output->getContext());
            return;
        }

        $filter = $output->filter ?? $this->defaultFilter;

        $source->push(
            sprintf("<?php echo {$filter}; ?>", trim($output->body)),
            $output->getContext()
        );
    }
}