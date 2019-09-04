<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Lexer\Grammar\RawGrammar;
use Spiral\Stempler\Lexer\GrammarInterface;
use Spiral\Stempler\Lexer\Lexer;
use Spiral\Stempler\Lexer\StreamInterface;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\Context;
use Spiral\Stempler\Parser\Syntax\RawSyntax;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * Module content parser with configurable grammars and syntaxes.
 */
final class Parser
{
    /** @var Lexer */
    private $lexer;

    /** @var string */
    private $path;

    /** @var SyntaxInterface[] */
    private $syntax = [];

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->syntax = [RawGrammar::class => new RawSyntax()];
    }

    /**
     * Associate template path with Parser (source-map).
     *
     * @param string|null $path
     * @return Parser
     */
    public function withPath(string $path = null): self
    {
        $parser = clone $this;
        $parser->path = $path;
        $parser->lexer = clone $this->lexer;

        foreach ($parser->syntax as $grammar => $stx) {
            $parser->syntax[$grammar] = clone $stx;
        }

        return $parser;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Add new parser grammar and syntax (registration order matter!).
     *
     * @param GrammarInterface $grammar
     * @param SyntaxInterface  $generator
     */
    public function addSyntax(GrammarInterface $grammar, SyntaxInterface $generator)
    {
        $this->lexer->addGrammar($grammar);
        $this->syntax[get_class($grammar)] = $generator;
    }

    /**
     * @param StreamInterface $stream
     * @return Template
     *
     * @throws ParserException
     */
    public function parse(StreamInterface $stream): Template
    {
        $template = new Template();

        try {
            $this->parseTokens(
                new Assembler($template, 'nodes'),
                $this->lexer->parse($stream)
            );
        } catch (SyntaxException $e) {
            throw new ParserException(
                $e->getMessage(),
                new Context($e->getToken(), $this->getPath()),
                $e
            );
        }

        return $template;
    }

    /**
     * @param Assembler $asm
     * @param iterable  $tokens
     *
     * @throws SyntaxException
     */
    public function parseTokens(Assembler $asm, iterable $tokens)
    {
        $node = $asm->getNode();

        $syntax = [];
        foreach ($this->syntax as $grammar => $stx) {
            $syntax[$grammar] = clone $stx;
        }

        foreach ($tokens as $token) {
            if (!isset($syntax[$token->grammar])) {
                throw new SyntaxException("Undefined token", $token);
            }

            $syntax[$token->grammar]->handle($this, $asm, $token);
        }

        if ($asm->getNode() !== $node) {
            throw new SyntaxException(
                "Invalid node hierarchy, unclosed " . $asm->getStackPath(),
                $asm->getNode()->getContext()->getToken()
            );
        }
    }
}