<?php

namespace TEIShredder;

use \TEIShredder;
use \UnexpectedValueException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Volume.
 * @package TEIShredder
 * @subpackage Tests
 */
class VolumeTest extends \PHPUnit_Framework_TestCase {

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
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->setup);
	}

	/**
	 * @test
	 */
	function createANewVolume() {
		$volume = new Volume($this->setup);
		$this->assertInstanceOf('\TEIShredder\Volume', $volume);
		return $volume;
	}

	/**
	 * @test
	 * @depends createANewVolume
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Volume $volume) {
		$volume->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewVolume
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage can not be set
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(Volume $volume) {
		$volume->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewVolume
	 */
	function setTheTitle(Volume $volume) {
		$volume->title = 'My book, volume IV';
		$this->assertEquals('My book, volume IV', $volume->title);
	}

	/**
	 * @test
	 * @depends createANewVolume
	 * @expectedException UnexpectedValueException
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(Volume $volume) {
		$volume->foo;
	}

	/**
	 * @test
	 */
	function saveANewVolume() {
		$volume = new Volume($this->setup);
		$volume->number = 6;
		$volume->title = 'My book, volume VI';
		$volume->pagenumber = 1234;
		$volume->save();
	}

}

