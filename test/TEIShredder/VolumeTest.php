<?php

namespace TEIShredder;

use LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\Volume.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\Volume
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Setup
     */
    protected $setup;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $this->setup = new Setup($pdoMock);
    }

    /**
     * Removes the fixture
     */
    public function tearDown()
    {
        unset($this->setup);
    }

    /**
     * @test
     */
    public function createANewVolume()
    {
        $volume = new Volume($this->setup);
        $this->assertInstanceOf('TEIShredder\Volume', $volume);
        return $volume;
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function tryingToGetPersistableDataForAnObjectWithIncompleteDataThrowsAnException()
    {
        $volume = new Volume($this->setup);
        $volume->persistableData();
    }

    /**
     * @test
     */
    public function gettingThePersistableDataForAnObjectWithCompleteDataSucceeds()
    {
        $volume             = new Volume($this->setup);
        $volume->title      = 'Foo';
        $volume->number     = 2;
        $volume->pagenumber = 123;
        $data               = $volume->persistableData();
        $this->assertInternalType('array', $data);
    }
}
