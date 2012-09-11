<?php

namespace TEIShredder;

use \TEIShredder;
use \UnexpectedValueException;
use \LogicException;

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
	 * @expectedException LogicException
	 */
	function tryingToGetPersistableDataForAnObjectWithIncompleteDataThrowsAnException() {
		$volume = new Volume($this->setup);
		$volume->persistableData();
	}

	/**
	 * @test
	 */
	function gettingThePersistableDataForAnObjectWithCompleteDataSucceeds() {
		$volume = new Volume($this->setup);
		$volume->title = 'Foo';
		$volume->number = 2;
		$volume->pagenumber = 123;
		$data = $volume->persistableData();
		$this->assertInternalType('array', $data);
	}
}

