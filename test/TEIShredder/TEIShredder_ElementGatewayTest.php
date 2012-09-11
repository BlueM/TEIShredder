<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_ElementDataMapper.
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
	function flushTheData() {
		ElementGateway::flush($this->setup);
		$objs = ElementGateway::findAll($this->setup);
		$this->assertTrue(0 == count($objs));
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
	 */
	function findAllElements() {

		$element = new Element($this->setup);
		$element->xmlid = 'element-1';
		$element->element = 'p';
		$element->page = 100;
		$element->chunk= 350;
		ElementGateway::save($this->setup, $element);

		$element = new Element($this->setup);
		$element->xmlid = 'element-2';
		$element->element = 'lg';
		$element->page = 200;
		$element->chunk= 250;
		ElementGateway::save($this->setup, $element);

		$objs = ElementGateway::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));
		$this->assertInstanceOf('\TEIShredder\Element', $objs[0]);
		$this->assertInstanceOf('\TEIShredder\Element', $objs[1]);
		$this->assertSame('lg', $objs[0]->element);
		$this->assertSame('element-1', $objs[1]->xmlid);
	}

}

