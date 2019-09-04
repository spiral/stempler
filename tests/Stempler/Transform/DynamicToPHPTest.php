<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Transform;

use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;

class DynamicToPHPTest extends BaseTest
{
    public function testOutput()
    {
        $doc = $this->parse('{{ $name }}');

        $this->assertInstanceOf(PHP::class, $doc->nodes[0]);
    }

    public function testDirective()
    {
        $doc = $this->parse('@foreach($users as $u) @endforeach');

        $this->assertInstanceOf(PHP::class, $doc->nodes[0]);
        $this->assertInstanceOf(PHP::class, $doc->nodes[2]);
    }

    public function testContextAwareEscapeSimpleEcho()
    {
        $this->assertSame(
            '<?php echo htmlspecialchars("hello world", ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>',
            $res = $this->compile('{{ "hello world" }}')->getContent()
        );

        $this->assertSame(
            'hello world',
            $this->eval($res)
        );
    }

    public function testContextAwareEscapeAttribute()
    {
        $this->assertSame(
            '<a href="<?php echo htmlspecialchars("hello world", ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>',
            $res = $this->compile('<a href="{{ "hello world" }}"></a>')->getContent()
        );

        $this->assertSame(
            '<a href="hello world"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim()
    {
        $this->assertSame(
            '<a style="color: <?php echo htmlspecialchars("hello world", ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>',
            $res = $this->compile('<a style="color: {{ "hello world" }}"></a>')->getContent()
        );

        $this->assertSame(
            '<a style="color: hello world"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim2()
    {
        $this->assertSame(
            '<a onclick="alert(<?php echo \'&quot;\', htmlspecialchars("hello world", ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'), \'&quot;\'; ?>)"></a>',
            $res = $this->compile('<a onclick="alert({{ "hello world" }})"></a>')->getContent()
        );

        $this->assertSame(
            '<a onclick="alert(&quot;hello world&quot;)"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim3()
    {
        $this->assertSame(
            '<script>alert(<?php echo json_encode("hello world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>',
            $res = $this->compile('<script>alert({{ "hello world" }})</script>')->getContent()
        );

        $this->assertSame(
            '<script>alert("hello world")</script>',
            $this->eval($res)
        );
    }

    public function testVerbatim4()
    {
        $this->assertSame(
            '<script>alert(<?php echo json_encode("hello\' \'world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>',
            $res = $this->compile('<script>alert({{ "hello\' \'world" }})</script>')->getContent()
        );

        $this->assertSame(
            '<script>alert("hello\u0027 \u0027world")</script>',
            $this->eval($res)
        );
    }

    protected function getVisitors(): array
    {
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        return [$dynamic];
    }

    private function eval(string $body): string
    {
        ob_start();

        eval('?>' . $body);

        return ob_get_clean();
    }
}
