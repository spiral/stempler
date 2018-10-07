<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\CompileException;
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

    /** @var Cache */
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
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
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
            new Loader($engine->loader, $this->processors),
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
        $className = $this->className($source, $context);

        if (class_exists($className, false)) {
            return $className;
        }

        $key = $this->cache->getKey($source, $className);
        $this->cache->load($key);

        if (class_exists($className, false)) {
            return $className;
        }

        // compiling body
        $content = $this->compileSource($className, $source, $context);
        $this->cache->write($key, $content);
        $this->cache->load($key);

        if (!class_exists($className, false)) {
            // unable to invoke template thought include_once, attempting to use
            // standard eval
            eval("?>{$content}");
        }

        if (!class_exists($className, false)) {
            throw new CompileException("Unable to compile `{$path}` template.");
        }

        return $className;
    }

    /**
     * @inheritdoc
     */
    public function reset(string $path, ContextInterface $context)
    {
        if (empty($this->cache)) {
            return;
        }

        $source = $this->getLoader()->load($path);
        $this->cache->delete($source, $this->className($source, $context));
    }

    /**
     * @inheritdoc
     */
    public function get(string $path, ContextInterface $context): ViewInterface
    {
        $className = $this->compile($path, $context);

        return new $className;
    }

    /**
     * Get unique template name.
     *
     * @param ViewSource       $source
     * @param ContextInterface $context
     * @return string
     */
    protected function className(ViewSource $source, ContextInterface $context)
    {
        return sprintf("StemplerView_%s", md5(
            $source->getNamespace() . '.' . $source->getName() . '.' . $context->getID()
        ));
    }

    /**
     * @param ViewSource $source
     * @return string
     */
    protected function normalize(ViewSource $source): string
    {
        return sprintf("%s:%s", $source->getNamespace(), $source->getName());
    }

    /**
     * @param string           $className
     * @param ViewSource       $source
     * @param ContextInterface $context
     * @return string
     */
    protected function compileSource(
        string $className,
        ViewSource $source,
        ContextInterface $context
    ): string {
        $content = $this->getCompiler($context)->compile($this->normalize($source));

        $source = $source->withCode($content);
        foreach ($this->postProcessors as $processor) {
            $source = $processor->process($source, $context);
        }

        return $this->generateClass($className, $source->getCode());
    }

    /**
     * Generate view class.
     *
     * @param string $className
     * @param string $body
     * @return string
     */
    protected function generateClass(string $className, string $body)
    {
        return "<?php
class {$className} extends Spiral\Stempler\StemplerView 
{
    protected function execute(array \$data) 
    {
        extract(\$data, EXTR_OVERWRITE);
        ?>{$body}<?php
    }
}";
    }
}