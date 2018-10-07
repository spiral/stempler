<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Stempler;

/**
 * Simple loader with ability to use multiple namespaces. Copy from TwigLoader.
 */
class StemplerLoader implements LoaderInterface
{
    /** @var \Spiral\Views\LoaderInterface */
    private $loader;

    /**
     * @param \Spiral\Views\LoaderInterface $loader
     */
    public function __construct(\Spiral\Views\LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(string $path): StemplerSource
    {
        return new StemplerSource($this->loader->load($path)->getFilename());
    }
}