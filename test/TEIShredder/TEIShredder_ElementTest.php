<?php

namespace TEIShredder;

use \TEIShredder;
use \SimpleXMLElement;

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
	 * @depends createANewElement
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Element $element) {
		$element->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewElement
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(Element $element) {
		$element->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewElement
	 */
	function setTheXmlId(Element $element) {
		$element->xmlid = 'my-xml-id';
		$this->assertEquals('my-xml-id', $element->xmlid);
	}

	/**
	 * @test
	 * @depends createANewElement
	 * @expectedException UnexpectedValueException
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(Element $element) {
		$element->foo;
	}

	/**
	 * @test
	 */
	function saveANewElement() {
		$element = new Element($this->setup);
		$element->xmlid = 'element-01';
		$element->element = 'rs';
		$element->page = 123;
		$element->chunk = 456;
		$element->save();
	}

	/**
	 * @test
	 */
	function flushTheData() {
		Element::flush($this->setup);
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAnId() {
		$element = new Element($this->setup);
		// $element->xmlid = 13;
		$element->element = 'rs';
		$element->page = 57;
		$element->chunk = 99;
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAVolume() {
		$element = new Element($this->setup);
		$element->xmlid = 13;
		// $element->element = 'rs';
		$element->page = 57;
		$element->chunk = 99;
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAPage() {
		$element = new Element($this->setup);
		$element->xmlid = 13;
		$element->element = 'rs';
		// $element->page = 57;
		$element->chunk = 99;
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAElementRequiresAnElement() {
		$element = new Element($this->setup);
		$element->xmlid = 13;
		$element->element = 'rs';
		$element->page = 57;
		// $element->chunk = 99;
		$element->save();
	}


}

