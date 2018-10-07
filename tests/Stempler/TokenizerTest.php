<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Tokenizer\Isolator;

class TokenizerTest extends TestCase
{
    public function testInput()
    {
        $tokenizer = new HtmlTokenizer(true, new Isolator());
        $tokens = $tokenizer->parse(file_get_contents(__DIR__ . '/../fixtures/htmlSample.php'));
        $this->assertNotEmpty($tokens);
    }

    public function testSerialization()
    {
        $tokenizer = new HtmlTokenizer(true, new Isolator());

        $tokens = $tokenizer->parse(file_get_contents(__DIR__ . '/../fixtures/htmlSample.php'));
        $this->assertNotEmpty($tokens);

        $result = '';
        foreach ($tokens as $token) {
            $result .= $token[HtmlTokenizer::TOKEN_CONTENT];
        }

        $this->assertSame(file_get_contents(__DIR__ . '/../fixtures/htmlSample.php'), $result);
    }

    public function testPersistent()
    {
        $tokenizer = new HtmlTokenizer(true, new Isolator());

        $tokens = $tokenizer->parse(file_get_contents(__DIR__ . '/../fixtures/htmlSample.php'));
        $this->assertNotEmpty($tokens);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'html',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<html>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[1]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'body',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<body>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[3]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'div',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<div style="background-color:black; color:white; margin:20px; padding:20px;">',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'style' => 'background-color:black; color:white; margin:20px; padding:20px;',
            ]
        ], $tokens[5]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'h2',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<h2>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[7]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'h2',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</h2>',
        ], $tokens[9]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[11]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[13]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p style="<?= "color: yellow" ?>">',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'style' => '<?= "color: yellow" ?>'
            ]
        ], $tokens[15]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[17]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[19]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'span',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<span style="color: red;">',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'style' => 'color: red;',
            ]
        ], $tokens[21]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'span',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</span>',
        ], $tokens[23]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[25]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p style="<%=ASP CODE%>">',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'style' => '<%=ASP CODE%>',
            ]
        ], $tokens[27]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'namespace:span',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<namespace:span <?= \'style="color: red"\' ?>>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                '<?= \'style="color: red"\' ?>' => null
            ]
        ], $tokens[29]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'namespace:span',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</namespace:span>',
        ], $tokens[33]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[35]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[37]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'img',
            HtmlTokenizer::TOKEN_TYPE       => 'short',
            HtmlTokenizer::TOKEN_CONTENT    => '<img src="http://url" alt="<DEMO> \'IMAGE\'"/>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'src' => 'http://url',
                'alt' => '<DEMO> \'IMAGE\'',
            ]
        ], $tokens[39]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[41]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'p',
            HtmlTokenizer::TOKEN_TYPE       => 'open',
            HtmlTokenizer::TOKEN_CONTENT    => '<p>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => []
        ], $tokens[43]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME       => 'input',
            HtmlTokenizer::TOKEN_TYPE       => 'short',
            HtmlTokenizer::TOKEN_CONTENT    => '<input type="checkbox" disabled prefix:attrbiute="ABC"/>',
            HtmlTokenizer::TOKEN_ATTRIBUTES => [
                'type'             => 'checkbox',
                'disabled'         => null,
                'prefix:attrbiute' => 'ABC',
            ]
        ], $tokens[45]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'p',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</p>',
        ], $tokens[47]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'div',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</div>',
        ], $tokens[49]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'body',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</body>',
        ], $tokens[51]);

        $this->assertSame([
            HtmlTokenizer::TOKEN_NAME    => 'html',
            HtmlTokenizer::TOKEN_TYPE    => 'close',
            HtmlTokenizer::TOKEN_CONTENT => '</html>',
        ], $tokens[53]);
    }

    public function testParseAndCompile()
    {
        $tokenizer = new HtmlTokenizer(true, new Isolator());

        $tokens = $tokenizer->parse($content = file_get_contents(__DIR__ . '/../fixtures/htmlSample.php'));
        $this->assertNotEmpty($tokens);

        //Pack back!
        $this->assertSame($content, $tokenizer->compile());
    }
}