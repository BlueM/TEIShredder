<?php

namespace TEIShredder;

use InvalidArgumentException;
use ReflectionMethod;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\AbstractGateway.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\AbstractGateway
 */
class AbstractGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unable to parse
     */
    public function tryingToFindObjectsWithAnUnparseableFilterStringThrowsAnException()
    {
        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $setup   = new Setup($pdoMock);

        $this->obj = $this->getMockForAbstractClass(
            'TEIShredder\AbstractGateway',
            array($setup->database, $setup->factory, $setup->prefix)
        );
        $this->performFind = new ReflectionMethod('TEIShredder\AbstractGateway', 'performFind');
        $this->performFind->setAccessible(true);

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
        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $setup   = new Setup($pdoMock);

        $this->obj = $this->getMockForAbstractClass(
            'TEIShredder\AbstractGateway',
            array($setup->database, $setup->factory, $setup->prefix)
        );
        $this->performFind = new ReflectionMethod('TEIShredder\AbstractGateway', 'performFind');
        $this->performFind->setAccessible(true);

        $this->assertEquals(
            'blah',
            $this->performFind->invoke($this->obj, 'Foo', array('id'), '', array('foo = bar'))
        );
    }

    /**
     * @test
     */
    public function performAFindWithLikeOperatorAnOrderByStatement()
    {
        $pdoStatementMock = $this->getMock('PDOStatement');
        $pdoStatementMock->expects($this->once())
            ->method('setFetchMode');
        $pdoStatementMock->expects($this->once())
            ->method('fetchAll');

        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $pdoMock->expects($this->any())
            ->method('quote')
            ->will($this->returnArgument(0));
        $pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains("foo LIKE %bla% ORDER BY xyz"))
            ->will($this->returnValue($pdoStatementMock));

        $setup = new Setup($pdoMock);

        $this->obj = $this->getMockForAbstractClass(
            'TEIShredder\AbstractGateway',
            array($setup->database, $setup->factory, $setup->prefix)
        );
        $this->performFind = new ReflectionMethod('TEIShredder\AbstractGateway', 'performFind');
        $this->performFind->setAccessible(true);

        $this->performFind->invoke($this->obj, 'Page', array('foo'), 'xyz', array('foo ~ bla'));
    }
}
