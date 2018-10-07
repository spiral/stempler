<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Import;

use Spiral\Stempler\ImportInterface;

/**
 * Declares to compiler that element must be treated as html tag, not Node include. Stop keyword
 * must be located in "stop" attribute of tag caused import.
 */
class Stop implements ImportInterface
{
    /*** @var string */
    protected $target = '';

    /**
     * @param string $element
     */
    public function __construct(string $element)
    {
        $this->target = $element;
    }

    /**
     * {@inheritdoc}
     */
    public function importable(string $element, array $token): bool
    {
        if ($this->target == '*') {
            //To disable every lower level importer, you can still define more importers after that
            return true;
        }

        return strtolower($element) == strtolower($this->target);
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath(string $element, array $token): ?string
    {
        return null;
    }
}