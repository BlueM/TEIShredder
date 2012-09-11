<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_SectionGateway.
 * @package TEIShredder
 * @subpackage Tests
 */
class SectionGatewayTest extends \PHPUnit_Framework_TestCase {

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
		SectionGateway::flush($this->setup);
		$objs = SectionGateway::findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveASection() {
		SectionGateway::flush($this->setup);

		$section = new Section($this->setup);
		$section->id = 5;
		$section->volume = 3;
		$section->title = "Section 5";
		$section->page = 55;
		$section->level = 1;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$obj = SectionGateway::find($this->setup, 5);
		$this->assertInstanceOf('\TEIShredder\Section', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchASectionGatewayByAnUnknownSectionGatewaynumberThrowsAnException() {
		SectionGateway::find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findASectionByItsId() {

		SectionGateway::flush($this->setup);

		// First, create object
		$section = new Section($this->setup);
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$obj = SectionGateway::find($this->setup, 17);
		$this->assertInstanceOf('\TEIShredder\Section', $obj);
		$this->assertEquals("Chapter 17", $section->title);
	}

	/**
	 * @test
	 */
	function findAllSections() {

		SectionGateway::flush($this->setup);

		$section = new Section($this->setup);
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$section = new Section($this->setup);
		$section->id = 23;
		$section->volume = 2;
		$section->title = "Chapter 23";
		$section->page = 180;
		$section->level = 2;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$objs = SectionGateway::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertSame(2, count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Section', $obj);
		}
	}

	/**
	 * @test
	 */
	function findAllSectionsInAVolume() {

		SectionGateway::flush($this->setup);

		$section = new Section($this->setup);
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$section = new Section($this->setup);
		$section->id = 23;
		$section->volume = 2;
		$section->title = "Chapter 23";
		$section->page = 180;
		$section->level = 2;
		$section->element = 'div';
		SectionGateway::save($this->setup, $section);

		$objs = SectionGateway::findAllInVolume($this->setup, 2);
		$this->assertInternalType('array', $objs);
		$this->assertSame(1, count($objs));
		$this->assertInstanceOf('\TEIShredder\Section', $objs[0]);
		$this->assertSame("Chapter 23", $objs[0]->title);
	}

}

