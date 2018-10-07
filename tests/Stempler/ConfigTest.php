<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Views\Processor\ContextProcessor;

class ConfigTest extends TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testWireConfigString()
    {
        $config = new StemplerConfig([
            'processors' => [ContextProcessor::class]
        ]);

        $this->assertInstanceOf(
            ContextProcessor::class,
            $config->getProcessors()[0]->resolve($this->container)
        );
    }

    public function testWireConfig()
    {
        $config = new StemplerConfig([
            'postProcessors' => [
                new Autowire(ContextProcessor::class)
            ]
        ]);

        $this->assertInstanceOf(
            ContextProcessor::class,
            $config->getPostProcessors()[0]->resolve($this->container)
        );
    }

    /**
     * @expectedException \Spiral\Stempler\Exception\ConfigException
     */
    public function testWireConfigException()
    {
        $config = new StemplerConfig([
            'processors' => [$this]
        ]);

        $config->getProcessors();
    }

}