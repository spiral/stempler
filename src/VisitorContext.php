<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Node\NodeInterface;

/**
 * Visitor context contains current node branch with all the previous nodes.
 */
final class VisitorContext
{
    /** @var NodeInterface[] */
    private $scope = [];

    /**
     * @param NodeInterface $node
     * @return VisitorContext
     */
    public function withNode(NodeInterface $node): self
    {
        $context = clone $this;
        $context->scope[] = $node;

        return $context;
    }

    /**
     * @return array
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    /**
     * @return NodeInterface|null
     */
    public function getCurrentNode(): ?NodeInterface
    {
        return $this->scope[count($this->scope) - 1] ?? null;
    }

    /**
     * @return NodeInterface|null
     */
    public function getParentNode(): ?NodeInterface
    {
        return $this->scope[count($this->scope) - 2] ?? null;
    }

    /**
     * @return NodeInterface|null
     */
    public function getFirstNode(): ?NodeInterface
    {
        return $this->scope[0] ?? null;
    }
}
