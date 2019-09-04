<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Directive;

use Spiral\Stempler\Directive\LoopDirective;

class LoopTest extends BaseTest
{
    protected const DIRECTIVES = [
        LoopDirective::class
    ];

    public function testForeachEndForeach()
    {
        $doc = $this->parse('@foreach($users as $u) {{ $u->name }} @endforeach');

        $this->assertSame(
            "<?php foreach(\$users as \$u): ?> <?php echo htmlspecialchars(\$u->name, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'); ?> <?php endforeach; ?>",
            $this->compile($doc)
        );
    }

    public function testWhileEndWhile()
    {
        $doc = $this->parse('@while(true) {! "OK" !} @endwhile');

        $this->assertSame(
            "<?php while(true): ?> <?php echo \"OK\"; ?> <?php endwhile; ?>",
            $this->compile($doc)
        );
    }

    public function testForEndFor()
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) {! $i !} @endfor');

        $this->assertSame(
            "<?php for(\$i=0; \$i<100; \$i++): ?> <?php echo \$i; ?> <?php endfor; ?>",
            $this->compile($doc)
        );
    }

    public function testBreak()
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @break @endfor');

        $this->assertSame(
            "<?php for(\$i=0; \$i<100; \$i++): ?> <?php break; ?> <?php endfor; ?>",
            $this->compile($doc)
        );
    }

    public function testBreak2()
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @break(2) @endfor');

        $this->assertSame(
            "<?php for(\$i=0; \$i<100; \$i++): ?> <?php break 2; ?> <?php endfor; ?>",
            $this->compile($doc)
        );
    }

    public function testContinue()
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @continue @endfor');

        $this->assertSame(
            "<?php for(\$i=0; \$i<100; \$i++): ?> <?php continue; ?> <?php endfor; ?>",
            $this->compile($doc)
        );
    }

    public function testContinue2()
    {
        $doc = $this->parse('@for($i=0; $i<100; $i++) @continue(2) @endfor');

        $this->assertSame(
            "<?php for(\$i=0; \$i<100; \$i++): ?> <?php continue 2; ?> <?php endfor; ?>",
            $this->compile($doc)
        );
    }
}