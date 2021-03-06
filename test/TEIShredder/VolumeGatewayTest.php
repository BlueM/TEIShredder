<?php

namespace TEIShredder;

use InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for VolumeGatewayTest
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\VolumeGateway
 */
class VolumeGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Setup
     */
    protected $setup;

    /**
     * @var VolumeGateway
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $setup     = prepare_default_data();
        $this->obj = new VolumeGateway($setup->database, $setup->factory, $setup->prefix);
    }

    /**
     * @test
     */
    public function flushTheData()
    {
        $this->obj->flush();
        $objs = $this->obj->find();
        $this->assertTrue(0 == count($objs));
    }

    /**
     * @test
     */
    public function saveAVolume()
    {
        $this->obj->flush();

        $volume             = new Volume;
        $volume->number     = 3;
        $volume->title      = "Hello world";
        $volume->pagenumber = 123;
        $this->obj->save($volume);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertInstanceOf('TEIShredder\Volume', $objs[0]);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function tryingToFindAVolumeByAnUnknownVolumeNumberThrowsAnException()
    {
        $this->obj->findByIdentifier(9999999);
    }

    /**
     * @test
     */
    public function findAVolumeByItsNumber()
    {
        $this->obj->flush();

        // First, create object
        $volume             = new Volume;
        $volume->number     = 17;
        $volume->title      = "Volume 17";
        $volume->pagenumber = 17;
        $this->obj->save($volume);

        $obj = $this->obj->findByIdentifier(17);
        $this->assertInstanceOf('TEIShredder\Volume', $obj);
        $this->assertEquals("Volume 17", $volume->title);
    }

    /**
     * @test
     */
    public function findAllVolumes()
    {
        $this->obj->flush();

        $volume             = new Volume;
        $volume->number     = 20;
        $volume->title      = "Volume 20";
        $volume->pagenumber = 20;
        $this->obj->save($volume);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertTrue(1 == count($objs));
        foreach ($objs as $obj) {
            $this->assertInstanceOf('TEIShredder\Volume', $obj);
        }
    }
}
