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
		VolumeDataMapper::flush($this->setup);
		$objs = VolumeDataMapper::findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveAVolume() {
		VolumeDataMapper::flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 3;
		$volume->title = "Hello world";
		$volume->pagenumber = 123;
		$volume->save();

		$obj = VolumeDataMapper::find($this->setup, 3);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAVolumeDataMapperByAnUnknownVolumeDataMappernumberThrowsAnException() {
		VolumeDataMapper::find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAVolumeByItsNumber() {

		VolumeDataMapper::flush($this->setup);

		// First, create object
		$volume = new Volume($this->setup);
		$volume->number = 17;
		$volume->title = "Volume 17";
		$volume->pagenumber = 17;
		$volume->save();

		$obj = VolumeDataMapper::find($this->setup, 17);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		$this->assertEquals("Volume 17", $volume->title);
	}

	/**
	 * @test
	 */
	function findAllVolumes() {

		VolumeDataMapper::flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 20;
		$volume->title = "Volume 20";
		$volume->pagenumber = 20;
		$volume->save();

		$objs = VolumeDataMapper::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		}
	}

}

