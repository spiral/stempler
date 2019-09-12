<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Compiler;
use Spiral\Stempler\Node\NodeInterface;

interface RendererInterface
{
    /**
     * @param Compiler      $compiler
     * @param Result        $result
     * @param NodeInterface $node
     * @return bool
     */
    public function render(Compiler $compiler, Result $result, NodeInterface $node): bool;
}
