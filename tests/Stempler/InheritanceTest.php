<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */
namespace Spiral\Tests\Stempler;

class InheritanceTest extends BaseTest
{
    public function testBaseA()
    {
        $result = $this->compile('base-a');

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file A(default).', $result[2]);
    }

    public function testBaseB()
    {
        $result = $this->compile('base-b');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file A(default).', $result[2]);
    }

    public function testBaseC()
    {
        $result = $this->compile('namespace:base-e');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file D(namespace). Base E.', $result[2]);
        $this->assertSame('Block C defined in file C(default).', $result[3]);
    }

    /**
     * @expectedException \Spiral\Stempler\Exceptions\StemplerException
     * @expectedExceptionMessage Unable to locate view 'includes-none.php' in namespace 'default'
     */
    public function testInvalidParent()
    {
        $result = $this->compile('includes-e');
    }
}