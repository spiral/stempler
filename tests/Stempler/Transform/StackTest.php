<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\Finalizer\StackCollector;
use Spiral\Stempler\Transform\Finalizer\TrimRaw;
use Spiral\Stempler\Transform\Visitor\DefineStacks;

class StackTest extends BaseTest
{
    public function testEmptyStack()
    {
        $doc = $this->parse('<stack:collect name="css"/>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertSame([], $doc->nodes[0]->nodes);
    }

    public function testDefaultStack()
    {
        $doc = $this->parse('<stack:collect name="css">css</stack:collect>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        $this->assertSame('css', $doc->nodes[0]->nodes[0]->content);
    }

    public function testStackPushAfter()
    {
        $doc = $this->parse('<stack:collect name="css"/><stack:push name="css">css</stack:push>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        $this->assertSame('css', $doc->nodes[0]->nodes[0]->content);
    }

    public function testStackPushAfterOrder()
    {
        $doc = $this->parse('<stack:collect name="css"/><stack:push name="css">css</stack:push><stack:push name="css">css2</stack:push>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertCount(1, $doc->nodes);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        $this->assertSame('css', $doc->nodes[0]->nodes[0]->content);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        $this->assertSame('css2', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPushBefore()
    {
        $doc = $this->parse('<stack:push name="css">css2</stack:push><stack:collect name="css"/><stack:push name="css">css</stack:push>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertCount(1, $doc->nodes);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        $this->assertSame('css2', $doc->nodes[0]->nodes[0]->content);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        $this->assertSame('css', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPrepend()
    {
        $doc = $this->parse('<stack:push name="css">css2</stack:push><stack:collect name="css"/><stack:prepend name="css">css</stack:prepend>');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[0]);
        $this->assertCount(1, $doc->nodes);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[0]);
        $this->assertSame('css', $doc->nodes[0]->nodes[0]->content);

        $this->assertInstanceOf(Raw::class, $doc->nodes[0]->nodes[1]);
        $this->assertSame('css2', $doc->nodes[0]->nodes[1]->content);
    }

    public function testPushFromTheSubtag()
    {
        $doc = $this->parse('
        <div><stack:push name="css">css2</stack:push></div>
        <stack:collect name="css"/>
        <div><stack:prepend name="css">css</stack:prepend></div>
        ');

        $this->assertInstanceOf(Aggregate::class, $doc->nodes[1]);
        $this->assertCount(3, $doc->nodes);

        $this->assertInstanceOf(Raw::class, $doc->nodes[1]->nodes[0]);
        $this->assertSame('css', $doc->nodes[1]->nodes[0]->content);

        $this->assertInstanceOf(Raw::class, $doc->nodes[1]->nodes[1]);
        $this->assertSame('css2', $doc->nodes[1]->nodes[1]->content);
    }

    public function testPushIntoSubtagOutofScope()
    {
        $this->assertSame(
            '<div></div><stack:push name="css">css2</stack:push>',
            $this->compile('<div><stack:collect name="css"/></div>
            <stack:push name="css">css2</stack:push>')->getContent()
        );
    }

    public function testPushIntoSubtagInTheScope()
    {
        $this->assertSame(
            '<div>css2</div>',
            $this->compile('
            <div><stack:collect name="css" level="1"/></div>
            <stack:push name="css">css2</stack:push>
            ')->getContent()
        );
    }

    public function testMultipleScopes()
    {
        $this->assertSame(
            'css2<div>css1</div>',
            $this->compile('
<stack:collect name="css"/>
<div>
    <stack:collect name="css"/>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>')->getContent()
        );
    }

    public function testScopeOverlap1()
    {
        $this->assertSame(
            '<div><div>css1</div></div><stack:push name="css">css2</stack:push>',
            $this->compile('
<div>
    <div>
        <stack:collect name="css" level="1"/>
    </div>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>')->getContent()
        );
    }

    public function testScopeOverlap2()
    {
        $this->assertSame(
            '<div><div>css1css2</div></div>',
            $this->compile('
<div>
    <div>
        <stack:collect name="css" level="2"/>
    </div>
    <stack:push name="css">css1</stack:push>
</div>
<stack:push name="css">css2</stack:push>')->getContent()
        );
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);

        // import resolution
        $builder->addVisitor(new StackCollector(), Builder::STAGE_FINALIZE);
        $builder->addVisitor(new TrimRaw(), Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineStacks()
        ];
    }
}
