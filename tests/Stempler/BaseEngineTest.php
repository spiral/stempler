<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfigFactory;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Loader\DirectoryLoader;
use Spiral\Core\BootloadManager;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Stempler\StemplerEngine;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

abstract class BaseEngineTest extends TestCase
{
    /** @var Container */
    protected $container;

    const BOOTLOADERS = [StemplerBootloader::class];

    public function setUp()
    {
        $this->container = $this->container ?? new Container();
        $this->container->bind(ConfigsInterface::class, ConfiguratorInterface::class);
        $this->container->bind(ConfiguratorInterface::class, new ConfigFactory(
            new DirectoryLoader(__DIR__ . '/../config/', $this->container),
            true
        ));

        $this->container->bind(ViewsInterface::class, ViewManager::class);

        (new BootloadManager($this->container))->bootload(static::BOOTLOADERS);
    }

    protected function getViews(): ViewManager
    {
        return $this->container->get(ViewsInterface::class);
    }

    protected function getStempler(): StemplerEngine
    {
        return $this->container->get(ViewsInterface::class)->getEngines()[0];
    }
}