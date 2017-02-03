<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Importers;

use Spiral\Stempler\ImporterInterface;

/**
 * {@inheritdoc}
 *
 * Simple aliased based import, declared relation between tag name and it's location. Element alias
 * must be located in "as" attribute caused import, location in "path" attribute (will be passed
 * thought Stempler->fetchLocation()).
 */
class Aliaser implements ImporterInterface
{
    /**
     * @var string
     */
    private $alias = '';

    /**
     * @var mixed
     */
    private $path = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $alias, string $path)
    {
        $this->alias = $alias;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function importable(string $element, array $token): bool
    {
        return strtolower($element) == strtolower($this->alias);
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath(string $element, array $token)
    {
        return $this->path;
    }
}