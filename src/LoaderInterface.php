<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\LoaderExceptionInterface;

/**
 * View loader interface. Pretty simple class which is compatible with Twig loader.
 */
interface LoaderInterface
{
    /**
     * Get source for given name.
     *
     * @param string $path
     *
     * @return StemplerSource
     *
     * @throws LoaderExceptionInterface
     */
    public function getSource(string $path): StemplerSource;
}