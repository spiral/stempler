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
use Spiral\Views\ViewSource;

class StemplerEngine implements EngineInterface
{
    protected const EXTENSION = 'dark';

    /** @var StemplerCache|null */
    private $cache = null;

    /** @var LoaderInterface|null */
    private $loader = null;

    /** @var Compiler|null */
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
            new StemplerLoader($engine->loader, $this->processors),
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
     * Return compiler locked into specific context.
     *
     * @param ContextInterface $context
     * @return CompilerInterface
     */
    public function getCompiler(ContextInterface $context): CompilerInterface
    {
        if (empty($this->compiler)) {
            throw new EngineException("No associated compiler found.");
        }

        $this->compiler->getLoader()->setContext($context);

        return $this->compiler;
    }

    /**
     * @inheritdoc
     */
    public function compile(string $path, ContextInterface $context)
    {
        $source = $this->getLoader()->load($path);

        $content = $this->getCompiler($context)->compile($this->normalize($source));

        $source = $source->withCode($content);

        foreach ($this->postProcessors as $processor) {
            $source = $processor->process($source, $context);
        }

        return $source->getCode();

        //todo: map exception
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
        return $this->compile($path, $context);
    }

    /**
     * @param ViewSource $source
     * @return string
     */
    protected function normalize(ViewSource $source): string
    {
        return sprintf("%s:%s", $source->getNamespace(), $source->getName());
    }
}