<?php

namespace TEIShredder;

use \TEIShredder;
use \SimpleXMLNamedEntity;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_NamedEntity.
 * @package TEIShredder
 * @subpackage Tests
 */
class NamedEntityTest extends \PHPUnit_Framework_TestCase {

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
	function createANewNamedEntity() {
		$entity = new NamedEntity($this->setup);
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $entity);
		return $entity;
	}

	/**
	 * @test
	 * @depends createANewNamedEntity
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(NamedEntity $element) {
		$element->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewNamedEntity
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(NamedEntity $element) {
		$element->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewNamedEntity
	 */
	function setTheXmlId(NamedEntity $element) {
		$element->xmlid = 'my-xml-id';
		$this->assertEquals('my-xml-id', $element->xmlid);
	}

	/**
	 * @test
	 * @depends createANewNamedEntity
	 * @expectedException UnexpectedValueException
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(NamedEntity $element) {
		$element->foo;
	}

	/**
	 * @test
	 */
	function saveANewNamedEntity() {
		$element = new NamedEntity($this->setup);
		$element->xmlid = 'element-01';
		$element->page = 123;
		$element->domain = 'person';
		$element->key = 'http://d-nb.info/gnd/118582143';
		$element->contextstart = 'Painter ';
		$element->notation = 'Michelangelo';
		$element->contextend = ' lived in the renaissance';
		$element->container = 'p';
		$element->chunk = 456;
		$element->notationhash = 'd1f9cc6d';
		$element->save();
	}

	/**
	 * @test
	 */
	function flushTheData() {
		NamedEntity::flush($this->setup);
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresAPageNumber() {
		$element = new NamedEntity($this->setup);
		// $element->page = 123;
		$element->domain = 'person';
		$element->key = 'http://d-nb.info/gnd/118582143';
		$element->notation = 'Michelangelo';
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresADomain() {
		$element = new NamedEntity($this->setup);
		$element->page = 123;
		// $element->domain = 'person';
		$element->key = 'http://d-nb.info/gnd/118582143';
		$element->notation = 'Michelangelo';
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresAKey() {
		$element = new NamedEntity($this->setup);
		$element->page = 123;
		$element->domain = 'person';
		// $element->key = 'http://d-nb.info/gnd/118582143';
		$element->notation = 'Michelangelo';
		$element->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresANotation() {
		$element = new NamedEntity($this->setup);
		$element->page = 123;
		$element->domain = 'person';
		$element->key = 'http://d-nb.info/gnd/118582143';
		// $element->notation = 'Michelangelo';
		$element->save();
	}

}

