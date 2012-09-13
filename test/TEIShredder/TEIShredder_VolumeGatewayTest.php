<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for VolumeGatewayTest
 * @package TEIShredder
 * @subpackage Tests
 */
class VolumeGatewayTest extends \PHPUnit_Framework_TestCase {

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
		$vg = new VolumeGateway;
		$vg->flush($this->setup);
		$objs = $vg->findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveAVolume() {
		$vg = new VolumeGateway;
		$vg->flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 3;
		$volume->title = "Hello world";
		$volume->pagenumber = 123;
		$vg->save($this->setup, $volume);

		$obj = $vg->find($this->setup, 3);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFindAVolumeByAnUnknownVolumeNumberThrowsAnException() {
		$vg = new VolumeGateway;
		$vg->find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAVolumeByItsNumber() {

		$vg = new VolumeGateway;
		$vg->flush($this->setup);

		// First, create object
		$volume = new Volume($this->setup);
		$volume->number = 17;
		$volume->title = "Volume 17";
		$volume->pagenumber = 17;
		$vg->save($this->setup, $volume);

		$obj = $vg->find($this->setup, 17);
		$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		$this->assertEquals("Volume 17", $volume->title);
	}

	/**
	 * @test
	 */
	function findAllVolumes() {

		$vg = new VolumeGateway;
		$vg->flush($this->setup);

		$volume = new Volume($this->setup);
		$volume->number = 20;
		$volume->title = "Volume 20";
		$volume->pagenumber = 20;
		$vg->save($this->setup, $volume);

		$objs = $vg->findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Volume', $obj);
		}
	}

}

