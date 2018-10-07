<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */
namespace Spiral\Tests\Stempler;

class ImportsTest extends BaseTest
{
    public function testImportNone()
    {
        $result = $this->compile('import-a');

        $this->assertSame('Base file.', $result[0]);
        $this->assertSame('<tag name="test">value</tag>', $result[1]);
    }

    public function testImportAsAlias()
    {
        $result = $this->compile('import-b');

        $this->assertSame('Base file.', $result[0]);
        $this->assertSame('<tag class="tag-b" name="test">value</tag>', $result[1]);
    }

    public function testImportWithPrefix()
    {
        $result = $this->compile('import-c');

        $this->assertSame('Base file.', $result[0]);
        $this->assertSame('<tag name="test">value</tag>', $result[1]);
        $this->assertSame('<tag class="tag-b" name="test">value</tag>', $result[2]);
    }


    public function testImportBundle()
    {
        $result = $this->compile('import-bundle');

        $this->assertSame('<tag name="1" id="1">inner-1</tag>', $result[0]);
        $this->assertSame('<tag class="tag-b" name="2">inner-2</tag>', $result[1]);
    }

    public function testImportBundleString()
    {
        $result = $this->compileString('
<dark:use bundle="includes/bundle"/>
<tag-1 name="1" id="1">inner-1</tag-1>
<tag-2 name="2">inner-2</tag-2>
');

        $this->assertSame('<tag name="1" id="1">inner-1</tag>', $result[0]);
        $this->assertSame('<tag class="tag-b" name="2">inner-2</tag>', $result[1]);
    }

    public function testImportBundleStringWithStopped()
    {
        $result = $this->compileString('
<dark:use bundle="includes/bundle"/>
<dark:use stop="tag-2"/>

<tag-1 name="1" id="1">inner-1</tag-1>
<tag-2 name="2">inner-2</tag-2>
');

        $this->assertSame('<tag name="1" id="1">inner-1</tag>', $result[0]);
        $this->assertSame('<tag-2 name="2">inner-2</tag-2>', $result[1]);
    }

    public function testImportBundleStringWithStoppedInherited()
    {
        $result = $this->compileString('
            <extends:import-bundle/>
            <dark:use stop="tag-2"/>

            <block:content>
                <yield:content/>
                <tag-2 name="2">inner-2</tag-2>
            </block:content>
        ');

        $this->assertSame('<tag name="1" id="1">inner-1</tag>', $result[0]);
        $this->assertSame('<tag class="tag-b" name="2">inner-2</tag>', $result[1]);

        //Newly added after stopper
        $this->assertSame('<tag-2 name="2">inner-2</tag-2>', $result[2]);
    }

    /**
     * @expectedException \Spiral\Stempler\Exception\StemplerException
     * @expectedExceptionMessage Unable to locate view 'includes/tag-c.php' in namespace 'default'
     */
    public function testImportWithPrefixErrorTag()
    {
        $result = $this->compile('import-d');

        $this->assertSame('Base file.', $result[0]);
        $this->assertSame('<tag class="tag-b" name="test">value</tag>', $result[1]);
    }

    /**
     * @expectedException \Spiral\Stempler\Exception\SyntaxException
     * @expectedExceptionMessage Undefined use element
     */
    public function testInvalidUseElement()
    {
        $result = $this->compile('import-e');

        $this->assertSame('Base file.', $result[0]);
        $this->assertSame('<tag class="tag-b" name="test">value</tag>', $result[1]);
    }
}