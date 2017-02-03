<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

/**
 * Provides ability to compose multiple html files together.
 */
class Stempler
{
    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var SyntaxInterface
     */
    protected $syntax = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param LoaderInterface $loader
     * @param SyntaxInterface $syntax
     * @param array           $options
     */
    public function __construct(
        LoaderInterface $loader,
        SyntaxInterface $syntax,
        array $options = []
    ) {
        $this->loader = $loader;
        $this->syntax = $syntax;
        $this->options = $options;
    }

    /**
     * Compile path.
     *
     * @param string $path
     *
     * @return string
     */
    public function compile(string $path): string
    {
        return $this->getSupervisor()->createNode($path)->compile();
    }

    /**
     * Compile string template.
     *
     * @param string $source
     *
     * @return string
     */
    public function compileString(string $source): string
    {
        $node = new Node($this->getSupervisor(), 'root', $source);

        return $node->compile();
    }

    /**
     * Create new instance of supervisor.
     *
     * @return Supervisor
     */
    protected function getSupervisor(): Supervisor
    {
        return new Supervisor($this->loader, $this->syntax);
    }
}