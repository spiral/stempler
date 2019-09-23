<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

if (!function_exists('inject')) {
    /**
     * Macro function to be replaced by the injected value.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    function inject(string $name, $default = null)
    {
        return $default;
    }
}
