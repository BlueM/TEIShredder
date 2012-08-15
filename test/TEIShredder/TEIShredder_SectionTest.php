<?php

namespace TEIShredder;

use \TEIShredder;
use \SimpleXMLElement;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Section.
 * @package TEIShredder
 * @subpackage Tests
 */
class SectionTest extends \PHPUnit_Framework_TestCase {

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
	function createANewSection() {
		$section = new Section($this->setup);
		$this->assertInstanceOf('\TEIShredder\Section', $section);
		return $section;
	}

	/**
	 * @test
	 * @depends createANewSection
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Section $section) {
		$section->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewSection
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage can not be set
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(Section $section) {
		$section->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewSection
	 */
	function setTheTitle(Section $section) {
		$section->title = 'Chapter 2';
		$this->assertEquals('Chapter 2', $section->title);
	}

	/**
	 * @test
	 * @depends createANewSection
	 * @expectedException UnexpectedValueException
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(Section $section) {
		$section->foo;
	}

	/**
	 * @test
	 */
	function saveANewSection() {
		$section = new Section($this->setup);
		$section->id = 13;
		$section->volume = 2;
		$section->title = 'Chapter 17';
		$section->page = 57;
		$section->level = 2;
		$section->element = 'div';
		$section->xmlid = 'section-12345';
		$section->save();
	}

	/**
	 * @test
	 */
	function flushTheData() {
		Section::flush($this->setup);
	}

}

