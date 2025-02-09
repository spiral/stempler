<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Compiler;

use Spiral\Stempler\Compiler\Renderer\CoreRenderer;

class RawTest extends BaseTestCase
{
    protected const RENDERS = [
        CoreRenderer::class,
    ];

    public function testCompileRaw(): void
    {
        $doc = $this->parse('hello world');

        self::assertSame('hello world', $this->compile($doc));
    }
}
