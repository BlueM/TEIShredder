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
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$setup = prepare_default_data();
		$this->obj = new ElementGateway($setup->database, $setup->factory, $setup->prefix);
	}

	/**
	 * @test
	 */
	function saveANewElement() {
		$element = new Element();
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		$this->obj->save($element);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAnElementByAnUnknownXmlidThrowsAnException() {
		$this->obj->find('element-123');
	}

	/**
	 * @test
	 */
	function findAnElementByItsXmlid() {

		$this->obj->flush();

		// First, create object
		$element = new Element();
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		$this->obj->save($element);

		$obj = $this->obj->find('pb-15');
		$this->assertInstanceOf('\TEIShredder\Element', $obj);
		$this->assertEquals('div', $element->element);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function flushTheData() {
		// First, create object
		$element = new Element();
		$element->xmlid = "pb-15";
		$element->element = 'div';
		$element->page = 23;
		$element->chunk = 234;
		$this->obj->save($element);

		$this->obj->flush();

		// Now, we shouldn’t be able to find the element
		$this->obj->find('pb-15');
	}

}

