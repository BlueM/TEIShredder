<?php

namespace TEIShredder;

use \LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Volume.
 *
 * @package    TEIShredder
 * @subpackage Tests
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
        $this->setup = prepare_default_data();
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
        $this->assertInstanceOf('\TEIShredder\Volume', $volume);
        return $volume;
    }

    /**
     * @test
     * @expectedException LogicException
     */
    function tryingToGetPersistableDataForAnObjectWithIncompleteDataThrowsAnException()
    {
        $volume = new Volume($this->setup);
        $volume->persistableData();
    }

    /**
     * @test
     */
    function gettingThePersistableDataForAnObjectWithCompleteDataSucceeds()
    {
        $volume             = new Volume($this->setup);
        $volume->title      = 'Foo';
        $volume->number     = 2;
        $volume->pagenumber = 123;
        $data               = $volume->persistableData();
        $this->assertInternalType('array', $data);
    }
}
