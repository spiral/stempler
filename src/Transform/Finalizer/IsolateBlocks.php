<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Transform\Finalizer;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

final class IsolateBlocks implements VisitorInterface
{
    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Block) {
            $node->name = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {

    }
}