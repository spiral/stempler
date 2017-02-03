<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Behaviours;

use Spiral\Stempler\BehaviourInterface;

/**
 * Defines new block.
 */
class InnerBlock implements BehaviourInterface
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Created block name.
     *
     * @return string
     */
    public function blockName(): string
    {
        return $this->name;
    }
}