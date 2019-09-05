<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Tests\Transform\BaseTest;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class SourcemapTest extends BaseTest
{
    public function testSimpleLoad()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('bundle-import');

        $this->assertSame(
            '<a href="abc">cde</a>',
            trim($res->getContent())
        );
    }

    public function testGetTemplates()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('bundle-import');

        $this->assertSame([
            'bundle-import',
            'import/bundle'
        ], $res->getPaths());
    }

    public function testPHPImportResult()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        $this->assertSame(preg_replace("/\s+/", "", '
<div>                                                                                   
    <?php foreach ([\'a\', \'b\', \'c\'] as $value): ?>                                                                                                              
    <b><?php echo htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?></b>                                                                                    
    <?php endforeach; ?>                                                                   
</div>'),
            preg_replace("/\s+/", "", $res->getContent()));
    }

    public function testCompress()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $sm2 = unserialize(serialize($sm));

        $this->assertEquals($sm, $sm2);
    }

    public function testGetStack()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('import-php');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $stack = $sm->getStack(6);
        $this->assertCount(3, $stack);
    }

    public function testTripeImportAndExtend()
    {
        $res = $this->getBuilder($this->getFixtureLoader())->compile('demo-import');

        $sm = $res->getSourceMap($this->getFixtureLoader());

        $stack = $sm->getStack(12);
        $this->assertCount(5, $stack);
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors = []): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);
        $builder->addVisitor(new ExtendsParent($builder), Builder::STAGE_TRANSFORM);

        // so we can inject into PHP
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        $builder->addVisitor($dynamic, Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineAttributes(),
            new DefineBlocks()
        ];
    }
}