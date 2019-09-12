<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Grammar;

use Spiral\Stempler\Lexer\Grammar\Dynamic\DeclareGrammar;
use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\Token;

class DynamicTest extends BaseTest
{
    protected const GRAMMARS = [DynamicGrammar::class];

    public function testRaw()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, 'raw body')
            ],
            ('raw body')
        );
    }

    public function testEcho()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $var '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 8, '}}')
            ],
            ('{{ $var }}')
        );
    }

    public function testEchoWithString()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $var . "{{ hello world }}" '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 30, '}}')
            ],
            ('{{ $var . "{{ hello world }}" }}')
        );
    }

    public function testEchoRaw()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_RAW_TAG, 0, '{!!'),
                new Token(DynamicGrammar::TYPE_BODY, 3, ' $var '),
                new Token(DynamicGrammar::TYPE_CLOSE_RAW_TAG, 9, '!!}')
            ],
            ('{!! $var !!}')
        );
    }

    public function testInvalidEcho()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '{! $var }}'),
            ],
            ('{! $var }}')
        );
    }

    public function testInvalidEcho2()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '{{ $var !}'),
            ],
            ('{{ $var !}')
        );
    }

    public function testEscapedEcho()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 1, '{{ $var }}'),
            ],
            ('@{{ $var }}')
        );
    }

    public function testEscapedRawEcho()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 1, '{!! $var !!}'),
            ],
            ('@{!! $var !!}')
        );
    }

    public function testDirective()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
            ],
            ('@do')
        );
    }

    public function testDirectiveAfterRaw()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, ' '),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 1, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 2, 'do'),
            ],
            (' @do')
        );
    }

    public function testDirectiveBeforeRaw()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(Token::TYPE_RAW, 3, ' '),
            ],
            ('@do ')
        );
    }

    public function testDirectiveBeforeRawAndValue()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(Token::TYPE_RAW, 3, ' ok'),
            ],
            ('@do ok')
        );
    }

    public function testDirectiveEmbedded()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '"'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 1, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 2, 'do'),
                new Token(Token::TYPE_RAW, 4, '"'),
            ],
            ('"@do"')
        );
    }

    public function testDirectiveAfterDirective()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 3, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 4, 'other'),
            ],
            ('@do@other')
        );
    }

    public function testDirectiveWithBody()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 11, ')'),
            ],
            ('@do(var=foo)')
        );
    }

    public function testDirectiveWithBodyConsecutive()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 11, ')'),
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 12, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 13, 'other'),
            ],
            ('@do(var=foo)@other')
        );
    }

    public function testDirectiveWithNestedParenthesis()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var=(foo+(1))'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 17, ')'),
            ],
            ('@do(var=(foo+(1)))')
        );
    }

    public function testDirectiveWhitespaceBeforeBody()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_WHITESPACE, 3, ' '),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 4, '('),
                new Token(DynamicGrammar::TYPE_BODY, 5, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 12, ')'),
            ],
            ('@do (var=foo)')
        );
    }

    public function testDirectiveMultipleWhitespaceBeforeBody()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_WHITESPACE, 3, '  '),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 5, '('),
                new Token(DynamicGrammar::TYPE_BODY, 6, 'var=foo'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 13, ')'),
            ],
            ('@do  (var=foo)')
        );
    }

    public function testDirectiveWithQuoteInBody()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_DIRECTIVE, 0, '@'),
                new Token(DynamicGrammar::TYPE_KEYWORD, 1, 'do'),
                new Token(DynamicGrammar::TYPE_BODY_OPEN, 3, '('),
                new Token(DynamicGrammar::TYPE_BODY, 4, 'var="(foo"'),
                new Token(DynamicGrammar::TYPE_BODY_CLOSE, 14, ')'),
                new Token(Token::TYPE_RAW, 15, ')'),
            ],
            ('@do(var="(foo"))')
        );
    }

    public function testInvalidDirective()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '@do(var=abc'),
            ],
            ('@do(var=abc')
        );
    }

    public function testDeclareDirective()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 8, ' ok'),
            ],
            ('@declare ok')
        );
    }

    public function testDeclareWithBodyDirective()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 15, ' ok'),
            ],
            ('@declare(hello) ok')
        );
    }

    public function testDeclareSyntaxOff()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 0, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 2, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 9, '}}'),
                new Token(Token::TYPE_RAW, 37, '{{ $name }}')
            ],
            ('{{ $name }}@declare( syntax = "off" ){{ $name }}')
        );
    }

    public function testDeclareSyntaxOn()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 20, '{{ $name }}'),
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 50, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 52, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 59, '}}'),
            ],
            ('@declare(syntax=off){{ $name }}@declare(syntax=on){{ $name }}')
        );
    }

    public function testDeclareCustomSyntax()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 31, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 33, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 40, '%}'),
            ],
            ('@declare(open="{%", close="%}"){% $name %}')
        );
    }

    public function testDeclareRawCustomSyntax()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_RAW_TAG, 37, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 39, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_RAW_TAG, 46, '%}'),
            ],
            ('@declare(openRaw="{%", closeRaw="%}"){% $name %}')
        );
    }

    public function testDeclareCustomDefault()
    {
        $this->assertTokens(
            [
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 31, '{%'),
                new Token(DynamicGrammar::TYPE_BODY, 33, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 40, '%}'),
                new Token(DynamicGrammar::TYPE_OPEN_TAG, 68, '{{'),
                new Token(DynamicGrammar::TYPE_BODY, 70, ' $name '),
                new Token(DynamicGrammar::TYPE_CLOSE_TAG, 77, '}}'),
            ],
            ('@declare(open="{%", close="%}"){% $name %}@declare(syntax="default"){{ $name }}')
        );
    }

    public function testTokenName()
    {
        $this->assertSame('DECLARE:KEYWORD', DeclareGrammar::tokenName(DeclareGrammar::TYPE_KEYWORD));
        $this->assertSame('DECLARE:EQUAL', DeclareGrammar::tokenName(DeclareGrammar::TYPE_EQUAL));
        $this->assertSame('DECLARE:COMMA', DeclareGrammar::tokenName(DeclareGrammar::TYPE_COMMA));
        $this->assertSame('DECLARE:QUOTED', DeclareGrammar::tokenName(DeclareGrammar::TYPE_QUOTED));
    }
}