<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Grammar;

use Spiral\Stempler\Lexer\Grammar\HTMLGrammar;
use Spiral\Stempler\Lexer\Grammar\InlineGrammar;
use Spiral\Stempler\Lexer\Grammar\PHPGrammar;
use Spiral\Stempler\Lexer\Token;

class MixedTest extends BaseTest
{
    protected const GRAMMARS = [PHPGrammar::class, InlineGrammar::class, HTMLGrammar::class];

    public function testPHPTag()
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, '<?="my-tag"?>'),
                new Token(HTMLGrammar::TYPE_CLOSE, 14, '>'),
            ],
            ('<<?="my-tag"?>>')
        );
    }

    public function testNotPHPTag()
    {
        $this->assertTokens(
            [
                new Token(Token::TYPE_RAW, 0, '<<?"my-tag"?>>'),
            ],
            ('<<?"my-tag"?>>')
        );
    }

    public function testPHPAttribute()
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, '<?="my-tag"?>'),
                new Token(HTMLGrammar::TYPE_CLOSE, 14, '>'),
                new Token(PHPGrammar::TYPE_CODE, 15, '<?php echo "hello" ?>'),
                new Token(Token::TYPE_RAW, 36, 'end'),
            ],
            ('<<?="my-tag"?>><?php echo "hello" ?>end')
        );
    }

    public function testScriptWithPHP()
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 7, '>'),
                new Token(HTMLGrammar::TYPE_VERBATIM, 8, 'alert("<<?="a"?>>");'),
                new Token(HTMLGrammar::TYPE_OPEN_SHORT, 28, '</'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 30, 'script'),
                new Token(HTMLGrammar::TYPE_CLOSE, 36, '>'),
            ],
            ('<script>alert("<<?="a"?>>");</script>')
        );
    }

    public function testTagWithInlineValue()
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, 'tag'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 4, ' '),
                new Token(HTMLGrammar::TYPE_KEYWORD, 5, 'name'),
                new Token(HTMLGrammar::TYPE_EQUAL, 9, '='),
                new Token(HTMLGrammar::TYPE_ATTRIBUTE, 10, '"${name}"'),
                new Token(HTMLGrammar::TYPE_CLOSE, 19, '>'),
            ],
            ('<tag name="${name}">')
        );
    }

    public function testTagWithPHPAndInline()
    {
        $this->assertTokens(
            [
                new Token(HTMLGrammar::TYPE_OPEN, 0, '<'),
                new Token(HTMLGrammar::TYPE_KEYWORD, 1, '<?="my-tag"?>'),
                new Token(HTMLGrammar::TYPE_WHITESPACE, 14, ' '),
                new Token(HTMLGrammar::TYPE_KEYWORD, 15, 'name'),
                new Token(HTMLGrammar::TYPE_EQUAL, 19, '='),
                new Token(HTMLGrammar::TYPE_ATTRIBUTE, 20, '"${name}"'),
                new Token(HTMLGrammar::TYPE_CLOSE, 29, '>'),
            ],
            ('<<?="my-tag"?> name="${name}">')
        );
    }
}