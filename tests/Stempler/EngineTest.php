<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig\Tests\Twig;

use Spiral\Views\Context\ValueDependency;
use Spiral\Views\ViewContext;

class EngineTest extends BaseEngineTest
{
    public function testList()
    {
        $views = $this->getStempler()->getLoader()->list();
        $this->assertContains('default:test', $views);
        $this->assertContains('other:test', $views);
    }

    public function testRender()
    {
        $stempler = $this->getStempler();
        $this->assertSame(
            'test',
            $stempler->get('test', new ViewContext())->render([])
        );

        $this->assertSame(
            'other test',
            $stempler->get('other:test', new ViewContext())->render([])
        );
    }

    public function testRenderInContext()
    {
        $ctx = new ViewContext();
        $ctx = $ctx->withDependency(new ValueDependency('name', 'Test'));

        $stempler = $this->getStempler();
        $this->assertSame(
            'hello Anton of Test',
            $stempler->get('other:ctx', $ctx)->render(['name' => 'Anton'])
        );
    }

    /**
     * @expectedException \Spiral\Stempler\Exception\CompileException
     */
    public function testCompileException()
    {
        $stempler = $this->getStempler();
        $stempler->compile('other:error', new ViewContext());
    }
}