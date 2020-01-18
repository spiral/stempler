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
use Spiral\Stempler\Exception\DirectiveException;
use Spiral\Stempler\Exception\ExtendsException;
use Spiral\Stempler\Exception\ImportException;
use Spiral\Stempler\Exception\LoaderException;
use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Exception\StemplerException;
use Spiral\Stempler\Exception\SyntaxException;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Tests\Transform\BaseTest;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Merge\ExtendsParent;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ExceptionTest extends BaseTest
{
    public function testSimpleLoad(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        $this->assertSame(
            'hello world',
            $builder->compile('hello')->getContent()
        );
    }

    public function testSyntaxException(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('broken')->getContent();
        } catch (ParserException $e) {
            $this->assertInstanceOf(SyntaxException::class, $e->getPrevious());
            $this->assertContains('broken.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());
        }
    }

    public function testExceptionInElementImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-element');
        } catch (ImportException $e) {
            $this->assertContains('bad-element.dark.php', $e->getFile());
            $this->assertSame(1, $e->getLine());
        }
    }

    public function testExceptionInElementImport3rdLine(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-element-3');
        } catch (ImportException $e) {
            $this->assertContains('bad-element-3.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());
        }
    }

    public function testExceptionInDirImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-dir');
        } catch (ImportException $e) {
            $this->assertContains('bad-dir.dark.php', $e->getFile());
            $this->assertSame(1, $e->getLine());
        }
    }

    public function testExceptionInDirImport2(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('import/bad-dir-2');
        } catch (ImportException $e) {
            $this->assertContains('bad-dir-2.dark.php', $e->getFile());
            $this->assertSame(2, $e->getLine());
        }
    }

    public function testDirectiveException(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-directive');
        } catch (DirectiveException $e) {
            $this->assertContains('bad-directive.dark.php', $e->getFile());
            $this->assertSame(2, $e->getLine());
        }
    }

    public function testExceptionInImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('exception-in-import');
        } catch (ImportException $e) {
            $this->assertContains('exception-in-import.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());

            $e = $e->getPrevious();
            $this->assertInstanceOf(ImportException::class, $e);
            $this->assertContains('bad-element.dark.php', $e->getFile());
            $this->assertSame(1, $e->getLine());
        }
    }

    public function testSyntaxExceptionInImport(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('exception-in-import-2');
        } catch (ImportException $e) {
            $this->assertInstanceOf(ImportException::class, $e);
            $this->assertContains('exception-in-import-2.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());

            $e = $e->getPrevious();
            $this->assertInstanceOf(ParserException::class, $e);
            $this->assertContains('bundle2.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());
        }
    }

    public function testBadExtends(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-extends');
        } catch (ExtendsException $e) {
            $this->assertContains('bad-extends.dark.php', $e->getFile());
            $this->assertSame(1, $e->getLine());

            $this->assertInstanceOf(LoaderException::class, $e->getPrevious());
        }
    }

    public function testBadExtendsDueToSyntax(): void
    {
        $builder = $this->getBuilder($this->getFixtureLoader());

        try {
            $builder->compile('bad-extends-2');
        } catch (ExtendsException $e) {
            $this->assertInstanceOf(ExtendsException::class, $e);
            $this->assertContains('bad-extends-2.dark.php', $e->getFile());
            $this->assertSame(1, $e->getLine());

            $e = $e->getPrevious();
            $this->assertInstanceOf(ParserException::class, $e);
            $this->assertContains('broken.dark.php', $e->getFile());
            $this->assertSame(3, $e->getLine());
        }
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
