<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Behaviour;

use Spiral\Stempler\BehaviourInterface;

/**
 * Defines new block.
 */
final class CreateNode implements BehaviourInterface
{
    /** @var string */
    private $name = '';

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}