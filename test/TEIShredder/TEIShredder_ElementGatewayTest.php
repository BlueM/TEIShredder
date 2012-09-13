<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for ElementGatewayTest
 * @package TEIShredder
 * @subpackage Tests
 */
class ElementGatewayTest extends \PHPUnit_Framework_TestCase {

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
	function saveANewElement() {
		$element = new Element($this->setup);
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		ElementGateway::save($this->setup, $element);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAnElementByAnUnknownXmlidThrowsAnException() {
		ElementGateway::find($this->setup, 'element-123');
	}

	/**
	 * @test
	 */
	function findAnElementByItsXmlid() {

		ElementGateway::flush($this->setup);

		// First, create object
		$element = new Element($this->setup);
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		ElementGateway::save($this->setup, $element);

		$obj = ElementGateway::find($this->setup, 'pb-15');
		$this->assertInstanceOf('\TEIShredder\Element', $obj);
		$this->assertEquals('div', $element->element);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function flushTheData() {

		// First, create object
		$element = new Element($this->setup);
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		ElementGateway::save($this->setup, $element);

		ElementGateway::flush($this->setup);

		// Now, we shouldn’t be able to find the element
		ElementGateway::find($this->setup, 'pb-15');
	}

}

