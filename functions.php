<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

if (!function_exists('value')) {
    /**
     * Macro function to be replaced by the injected value.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    function value(string $name, $default = null)
    {
        return $default;
    }
}
