<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Behaviour;

use Spiral\Stempler\BehaviourInterface;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\Node;

/**
 * Points node to it's parent.
 */
final class ExtendParent implements BehaviourInterface
{
    /** @var Node */
    private $parent = null;

    /** @var array */
    private $attributes = [];

    /** @var array */
    private $token = [];

    /**
     * @param Node  $parent
     * @param array $token
     */
    public function __construct(Node $parent, array $token)
    {
        $this->parent = $parent;
        $this->token = $token;
        $this->attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];
    }

    /**
     * Node which are getting extended.
     *
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->parent;
    }

    /**
     * Set of blocks (attributes) defined at moment of extend definition.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}