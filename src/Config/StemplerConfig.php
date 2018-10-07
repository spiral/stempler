<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Stempler\Exception\ConfigException;

class StemplerConfig extends InjectableConfig
{
    const CONFIG = "views/stempler";

    /** @var array */
    protected $config = [
        'processors'     => [],
        'postProcessors' => []
    ];

    /**
     * @return Autowire[]
     */
    public function getProcessors(): array
    {
        $processors = [];
        foreach ($this->config['processors'] as $processor) {
            $processors[] = $this->wire($processor);
        }

        return $processors;
    }

    /**
     * @return Autowire[]
     */
    public function getPostProcessors(): array
    {
        $processors = [];
        foreach ($this->config['postProcessors'] as $processor) {
            $processors[] = $this->wire($processor);
        }

        return $processors;
    }

    /**
     * @param mixed $item
     * @return Autowire
     *
     * @throws ConfigException
     */
    public function wire($item): Autowire
    {
        if ($item instanceof Autowire) {
            return $item;
        }

        if (is_string($item)) {
            return new Autowire($item);
        }

        throw new ConfigException("Invalid class reference in view config.");
    }
}