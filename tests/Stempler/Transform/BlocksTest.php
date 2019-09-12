<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Transform;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class BlocksTest extends BaseTest
{
    public function testDefineBlock()
    {
        $doc = $this->parse('<block:world>hello</block:world>');
        $this->assertInstanceOf(Block::class, $doc->nodes[0]);
        $this->assertSame('world', $doc->nodes[0]->name);
        $this->assertSame('hello', $doc->nodes[0]->nodes[0]->content);
    }

    public function testDefineShortBlock()
    {
        $doc = $this->parse('<block:world/>');
        $this->assertInstanceOf(Block::class, $doc->nodes[0]);
        $this->assertSame('world', $doc->nodes[0]->name);
        $this->assertSame([], $doc->nodes[0]->nodes);
    }

    protected function getVisitors(): array
    {
        return [new DefineBlocks()];
    }
}
