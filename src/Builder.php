<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Compiler\Result;
use Spiral\Stempler\Exception\CompilerException;
use Spiral\Stempler\Exception\ContextExceptionInterface;
use Spiral\Stempler\Exception\LoaderException;
use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Stempler\Node\Template;

/**
 * Builds and compiles templates using set given compiler. Template is passed thought the set of
 * visitors each within specific group.
 */
final class Builder
{
    // node visiting stages
    public const STAGE_PREPARE   = 0;
    public const STAGE_TRANSFORM = 1;
    public const STAGE_FINALIZE  = 2;

    /** @var LoaderInterface */
    private $loader;

    /** @var Parser */
    private $parser;

    /** @var Compiler */
    private $compiler;

    /** @var VisitorInterface[][] */
    private $visitors = [];

    /**
     * @param LoaderInterface $loader
     * @param Parser|null     $parser
     * @param Compiler|null   $compiler
     */
    public function __construct(LoaderInterface $loader, Parser $parser = null, Compiler $compiler = null)
    {
        $this->loader = $loader;
        $this->parser = $parser ?? new Parser();
        $this->compiler = $compiler ?? new Compiler();
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * @return Parser
     */
    public function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }

    /**
     * Add visitor to specific builder stage.
     *
     * @param VisitorInterface $visitor
     * @param int              $stage
     */
    public function addVisitor(VisitorInterface $visitor, int $stage = self::STAGE_PREPARE)
    {
        $this->visitors[$stage][] = $visitor;
    }

    /**
     * Compile template.
     *
     * @param string $path
     * @return Result
     *
     * @throws CompilerException
     * @throws \Throwable
     */
    public function compile(string $path): Result
    {
        $tpl = $this->load($path);

        try {
            return $this->compiler->compile($tpl);
        } catch (CompilerException $e) {
            throw $this->mapException($e);
        }
    }

    /**
     * @param string $path
     * @return Template
     *
     * @throws \Throwable
     */
    public function load(string $path): Template
    {
        $source = $this->loader->load($path);
        $stream = new StringStream($source->getContent());

        try {
            $tpl = $this->parser->withPath($path)->parse($stream);
        } catch (ParserException $e) {
            throw $this->mapException($e);
        }

        try {
            return $this->process($tpl);
        } catch (ContextExceptionInterface $e) {
            throw $this->mapException($e);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param Template $template
     * @return Template
     *
     * @throws \Throwable
     */
    protected function process(Template $template): Template
    {
        if (isset($this->visitors[self::STAGE_PREPARE])) {
            $traverser = new Traverser($this->visitors[self::STAGE_PREPARE]);
            $template = $traverser->traverse([$template])[0];
        }

        if (isset($this->visitors[self::STAGE_TRANSFORM])) {
            $traverser = new Traverser($this->visitors[self::STAGE_TRANSFORM]);
            $template = $traverser->traverse([$template])[0];
        }

        if (isset($this->visitors[self::STAGE_FINALIZE])) {
            $traverser = new Traverser($this->visitors[self::STAGE_FINALIZE]);
            $template = $traverser->traverse([$template])[0];
        }

        return $template;
    }

    /**
     * Set exception path and line.
     *
     * @param ContextExceptionInterface $e
     * @return ContextExceptionInterface
     */
    private function mapException(ContextExceptionInterface $e): ContextExceptionInterface
    {
        if ($e->getContext()->getPath() === null) {
            return $e;
        }

        try {
            $source = $this->loader->load($e->getContext()->getPath());
        } catch (LoaderException $te) {
            return $e;
        }

        if ($source->getFilename() === null) {
            return $e;
        }

        $e->setLocation(
            $source->getFilename(),
            Source::resolveLine($source->getContent(), $e->getContext()->getToken()->offset)
        );

        return $e;
    }
}