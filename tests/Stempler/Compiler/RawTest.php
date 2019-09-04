<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Compiler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;

class RawTest extends BaseTest
{
    protected const RENDERS = [
        CoreRenderer::class,
    ];

    public function testCompileRaw()
    {
        $doc = $this->parse('hello world');

        $this->assertSame('hello world', $this->compile($doc));
    }
}