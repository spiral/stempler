<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\FactoryInterface;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Stempler\StemplerCache;
use Spiral\Stempler\StemplerEngine;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Processor\ContextProcessor;

class StemplerBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        StemplerEngine::class => [self::class, 'stemplerEngine']
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     */
    public function boot(ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        $configurator->setDefaults('views/stempler', [
            'options'        => [],
            'processors'     => [
                ContextProcessor::class
            ],
            'postProcessors' => [

            ]
        ]);

        $configurator->modify(
            'views',
            new AppendPatch('engines', null, StemplerEngine::class)
        );

        if ($container->has('Spiral\Views\LocaleProcessor')) {
            $configurator->modify(
                'views/stempler',
                new AppendPatch('processors', null, 'Spiral\Views\LocaleProcessor')
            );
        }
    }

    /**
     * @param StemplerConfig   $config
     * @param ViewsConfig      $viewConfig
     * @param FactoryInterface $factory
     * @return StemplerEngine
     */
    protected function stemplerEngine(
        StemplerConfig $config,
        ViewsConfig $viewConfig,
        FactoryInterface $factory
    ): StemplerEngine {
        $engine = new StemplerEngine(
            $viewConfig->cacheEnabled() ? new StemplerCache($viewConfig->cacheDirectory()) : null
        );

        foreach ($config->getProcessors() as $extension) {
            $engine->addProcessor($extension->resolve($factory));
        }

        foreach ($config->getPostProcessors() as $processor) {
            $engine->addPostProcessor($processor->resolve($factory));
        }

        return $engine;
    }
}