<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Views\ContextInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewInterface;

class StemplerEngine implements EngineInterface
{
    protected const EXTENSION = 'dark';

    /** @var StemplerCache|null */
    private $cache = null;

    /** @var LoaderInterface|null */
    private $loader = null;

    /** @var CompilerInterface|null */
    private $compiler = null;

    /** @var ProcessorInterface[] */
    private $processors = [];

    /** @var ProcessorInterface[] */
    private $postProcessors = [];

    /**
     * @param StemplerCache|null $cache
     */
    public function __construct(StemplerCache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function addPostProcessor(ProcessorInterface $processor)
    {
        $this->postProcessors[] = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        $engine = clone $this;
        $engine->loader = $loader->withExtension(static::EXTENSION);

        $engine->compiler = new Compiler(
            new StemplerLoader($loader, $this->processors),
            new Syntax()
        );

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(): LoaderInterface
    {
        if (empty($this->loader)) {
            throw new EngineException("No associated loader found.");
        }

        return $this->loader;
    }

    /**
     * @inheritdoc
     */
    public function compile(string $path, ContextInterface $context)
    {

    }

    /**
     * @inheritdoc
     */
    public function reset(string $path, ContextInterface $context)
    {

    }

    /**
     * @inheritdoc
     */
    public function get(string $path, ContextInterface $context): ViewInterface
    {
        return null;
    }
}