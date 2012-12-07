<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;
use \ReflectionMethod;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for AbstractGateway
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class AbstractGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractGateway
     */
    protected $obj;

    /**
     * @var ReflectionMethod
     */
    protected $performFind;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {

        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $setup = new \TEIShredder\Setup($pdoMock);

        $this->obj = $this->getMockForAbstractClass(
            '\TEIShredder\AbstractGateway',
            array($setup->database, $setup->factory, $setup->prefix)
        );
        $this->performFind = new ReflectionMethod('\TEIShredder\AbstractGateway', 'performFind');
        $this->performFind->setAccessible(true);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to parse
     */
    public function tryingToFindObjectsWithAnUnparseableFilterStringThrowsAnException()
    {
        $this->assertEquals(
            'blah',
            $this->performFind->invoke($this->obj, 'Foo', array('id'), '', array('aaaaaa'))
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid property
     */
    public function tryingToFindObjectsWithAFilterStringThatUsesAnInvalidPropertyThrowsAnException()
    {
        $this->assertEquals(
            'blah',
            $this->performFind->invoke($this->obj, 'Foo', array('id'), '', array('foo = bar'))
        );
    }
}
