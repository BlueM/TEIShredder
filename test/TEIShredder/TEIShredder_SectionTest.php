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

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureASectionRequiresAnId() {
		$section = new Section($this->setup);
		// $section->id = 13;
		$section->volume = 2;
		$section->page = 57;
		$section->level = 2;
		$section->element = 'div';
		$section->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureASectionRequiresAVolume() {
		$section = new Section($this->setup);
		$section->id = 13;
		// $section->volume = 2;
		$section->page = 57;
		$section->level = 2;
		$section->element = 'div';
		$section->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureASectionRequiresAPage() {
		$section = new Section($this->setup);
		$section->id = 13;
		$section->volume = 2;
		// $section->page = 57;
		$section->level = 2;
		$section->element = 'div';
		$section->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureASectionRequiresAnElement() {
		$section = new Section($this->setup);
		$section->id = 13;
		$section->volume = 2;
		$section->page = 57;
		$section->level = 2;
		// $section->element = 'div';
		$section->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureASectionRequiresALevel() {
		$section = new Section($this->setup);
		$section->id = 13;
		$section->volume = 2;
		$section->page = 57;
		// $section->level = 2;
		$section->element = 'div';
		$section->save();
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchASectionByAnUnknownIdThrowsAnException() {
		Section::fetchSectionById($this->setup, 12345);
	}

	/**
	 * @test
	 */
	function fetchingASectionByTheIdWorks() {
		$section = new Section($this->setup);
		$section->id = 13;
		$section->volume = 2;
		$section->page = 57;
		$section->level = 2;
		$section->element = 'div';
		$section->save();

		$obj = Section::fetchSectionById($this->setup, 13);
		$this->assertInstanceOf('\TEIShredder\Section', $obj);
		$this->assertEquals(13, $obj->id);
		$this->assertEquals('div', $obj->element);
	}

	/**
	 * @test
	 */
	function fetchAllSectionsOfAVolume() {

		// Add the sections
		$s1 = new Section($this->setup);
		$s1->id = 20;
		$s1->volume = 2;
		$s1->page = 100;
		$s1->level = 1;
		$s1->element = 'div';
		$s1->save();

		$s2 = new Section($this->setup);
		$s2->id = 30;
		$s2->volume = 2;
		$s2->page = 200;
		$s2->level = 1;
		$s2->element = 'div';
		$s2->save();

		$sections = Section::fetchSectionsByVolume($this->setup, 2);
		$this->assertInternalType('array', $sections);
		$this->assertSame(2, count($sections));
		$this->assertInstanceOf('\TEIShredder\Section', $sections[0]);
		$this->assertInstanceOf('\TEIShredder\Section', $sections[1]);
		$this->assertSame('div', $sections[0]->element);
	}

}

