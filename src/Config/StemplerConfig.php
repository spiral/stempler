<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Config;

use Spiral\Core\InjectableConfig;

class StemplerConfig extends InjectableConfig
{
    const CONFIG = "views/stempler";

    /** @var array */
    protected $config = [
        'processors'     => [],
        'postProcessors' => []
    ];
}