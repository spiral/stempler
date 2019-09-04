<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Directive;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Compiler\Renderer\CoreRenderer;
use Spiral\Stempler\Compiler\Renderer\HTMLRenderer;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Syntax\DynamicSyntax;
use Spiral\Stempler\Parser\Syntax\HTMLSyntax;

abstract class BaseTest extends \Spiral\Stempler\Tests\Compiler\BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
        HTMLRenderer::class,
    ];

    protected const GRAMMARS = [
        DynamicGrammar::class => DynamicSyntax::class,
        HTMLGrammar::class    => HTMLSyntax::class
    ];

    protected const DIRECTIVES = [];

    /**
     * @param Template $document
     * @return string
     */
    protected function compile(Template $document): string
    {
        $compiler = new Compiler();
        foreach (static::RENDERS as $renderer) {
            $compiler->addRenderer(new $renderer);
        }

        $dynamic = new Compiler\Renderer\DynamicRenderer();
        foreach (static::DIRECTIVES as $directive) {
            $dynamic->addDirective(new $directive);
        }

        $compiler->addRenderer($dynamic);

        return $compiler->compile($document)->getContent();
    }
}