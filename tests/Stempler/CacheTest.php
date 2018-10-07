<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig\Tests\Twig;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\PatchInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Views\ViewContext;

class CacheTest extends BaseEngineTest
{
    /** @var FilesInterface */
    protected $files;

    public function setUp()
    {
        parent::setUp();

        $this->files = new Files();

        /** @var ConfiguratorInterface $configurator */
        $configurator = $this->container->get(ConfiguratorInterface::class);
        $configurator->modify('views', new EnableCachePatch());
    }

    public function testCache()
    {
        $this->assertCount(0, $this->files->getFiles(__DIR__ . '/../cache/', '*.php'));

        $twig = $this->getStempler();
        $this->assertSame('test', $twig->get('test', new ViewContext())->render([]));
        $this->assertCount(1, $this->files->getFiles(__DIR__ . '/../cache/', '*.php'));


        $twig->reset('test', new ViewContext());
        $this->assertCount(0, $this->files->getFiles(__DIR__ . '/../cache/', '*.php'));
    }
}

class EnableCachePatch implements PatchInterface
{
    public function patch(array $config): array
    {
        $config['cache']['enable'] = true;

        return $config;
    }
}