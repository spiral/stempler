<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Stempler\Tests\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Import\Element;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ImportElementTest extends BaseTest
{
    public function testNoImport()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', '<url href="google.com">hello world</url>');
        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<url href="google.com">hello world</url>',
            $builder->compile('root')->getContent()
        );
    }

    public function testSimpleImport()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="google.com">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testImportWithPHP()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="<?php echo \'google.com\'?>">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo \'google.com\'?>">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testImportWithOutput()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testStringValueIntoPHP()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}">hello world</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'context\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'hello world\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testOutputValueIntoPHPFromAttribute()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="<?php echo \'bad\'?>">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'bad\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testOutputValueIntoPHPFromAttributeUsingOutput()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="{{ \'OK\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'OK\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValue()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="hello {{ \'OK\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'hello \'.\'OK\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValueWithSpacing()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="{{ \'OK\' }}  {{ \'cool\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'OK\'.\'  \'.\'cool\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValueWithSpacingAround()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value=" {{ \'OK\' }} {{ \'cool\' }} ">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\' \'.\'OK\'.\' \'.\'cool\'.\' \'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testDefaultPHPValue()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}"></url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\', \'default\'.\'xxx\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper(\'default\'.\'xxx\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testDefaultPHPValueArray()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}"></url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(value(\'value\', [\'abc\'])) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars(strtoupper([\'abc\']), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testParentBlock()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="h"/><h><block:c>a<block:parent/></block:c></h>'
        );
        $loader->set('import', '<x c="${c|b}"></x>');

        $builder = $this->getBuilder($loader, []);
        $this->assertSame(
            '<x c="ab"></x>',
            $builder->compile('root')->getContent()
        );
    }

    public function testParentBlockShort()
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="h"/><h c="a ${parent}"/>'
        );
        $loader->set('import', '<x c="${c|b}"></x>');


        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<x c="a b"></x>',
            $builder->compile('root')->getContent()
        );
    }

    public function testElementPathAndAlias()
    {
        $element = new Element("path/to/import");
        $this->assertSame("path/to/import", $element->getPath());
        $this->assertSame("import", $element->getAlias());
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);

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
