<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_VolumeDataMapper.
 * @package TEIShredder
 * @subpackage Tests
 */
class VolumeDataMapperTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Setup
	 */
	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
	}

	/**
	 * @test
	 */
	function flushTheData() {
		VolumeGateway::flush($this->setup);
		$objs = VolumeGateway::findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveAVolume() {
		VolumeGateway::flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 3;
		$volume->title = "Hello world";
		$volume->pagenumber = 123;
		VolumeGateway::save($this->setup, $volume);

		$obj = VolumeGateway::find($this->setup, 3);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAVolumeDataMapperByAnUnknownVolumeDataMappernumberThrowsAnException() {
		VolumeGateway::find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAVolumeByItsNumber() {

		VolumeGateway::flush($this->setup);

		// First, create object
		$volume = new Volume($this->setup);
		$volume->number = 17;
		$volume->title = "Volume 17";
		$volume->pagenumber = 17;
		VolumeGateway::save($this->setup, $volume);

		$obj = VolumeGateway::find($this->setup, 17);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		$this->assertEquals("Volume 17", $volume->title);
	}

	/**
	 * @test
	 */
	function findAllVolumes() {

		VolumeGateway::flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 20;
		$volume->title = "Volume 20";
		$volume->pagenumber = 20;
		VolumeGateway::save($this->setup, $volume);

		$objs = VolumeGateway::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		}
	}

}

