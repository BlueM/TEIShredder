<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Element.
 * @package TEIShredder
 * @subpackage Tests
 */
class ElementTest extends \PHPUnit_Framework_TestCase {

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
	function createANewElement() {
		$element = new Element($this->setup);
		$this->assertInstanceOf('\TEIShredder\Element', $element);
		return $element;
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAnXmlid() {
		$element = new Element($this->setup);
		$element->element = 'rs';
		$element->page = 57;
		$element->chunk = 99;
		$element->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAPage() {
		$element = new Element($this->setup);
		$element->xmlid = 'element-01';
		$element->element = 'rs';
		$element->chunk = 99;
		$element->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAChunk() {
		$element = new Element($this->setup);
		$element->xmlid = 'element-01';
		$element->element = 'rs';
		$element->page = 57;
		$element->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAnElement() {
		$element = new Element($this->setup);
		$element->xmlid = 'element-01';
		$element->chunk = 123;
		$element->page = 57;
		$element->persistableData();
	}

	/**
	 * @test
	 */
	function getThePersistableDataOfAnObjectWithAllRequiredProperties() {
		$element = new Element($this->setup);
		$element->xmlid = 'element-01';
		$element->element = 'rs';
		$element->page = 57;
		$element->chunk = 99;
		$element->persistableData();
		$this->assertInternalType('array', $element->persistableData());
	}

}

