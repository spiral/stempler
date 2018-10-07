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
use Spiral\Stempler\ImporterInterface;
use Spiral\Stempler\Node;
use Spiral\Stempler\Supervisor;

/**
 * Points node to it's parent.
 */
class ExtendLayout implements BehaviourInterface
{
    /**
     * Parent (extended) node, treat it as page or element layout.
     *
     * @var Node
     */
    private $parent = null;

    /**
     * Attributes defined using extends tag.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
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
    public function parentNode(): Node
    {
        return $this->parent;
    }

    /**
     * Every importer defined in parent (extended node).
     *
     * @return ImporterInterface[]
     */
    public function parentImports(): array
    {
        $supervisor = $this->parent->getSupervisor();
        if (!$supervisor instanceof Supervisor) {
            return [];
        }

        return $supervisor->getImporters();
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