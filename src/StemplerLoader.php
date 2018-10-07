<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Views\ContextInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Traits\ProcessorTrait;
use Spiral\Views\ViewSource;

class StemplerLoader implements LoaderInterface
{
    use ProcessorTrait;

    /** @var LoaderInterface */
    private $loader;

    /** @var ContextInterface */
    private $context;

    /**
     * @param LoaderInterface $loader
     * @param array           $processors
     */
    public function __construct(LoaderInterface $loader, array $processors)
    {
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * Lock loader to specific context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function withExtension(string $extension): LoaderInterface
    {
        $loader = clone $this;
        $loader->loader = $loader->loader->withExtension($extension);

        return $loader;
    }

    /**
     * @inheritdoc
     */
    public function exists(string $path): bool
    {
        return $this->loader->exists($path);
    }

    /**
     * @inheritdoc
     */
    public function load(string $path): ViewSource
    {
        // todo: change it to load only when needed

        return $this->process($this->loader->load($path), $this->context);
    }

    /**
     * @inheritdoc
     */
    public function list(string $namespace = null): array
    {
        return $this->loader->list($namespace);
    }
}