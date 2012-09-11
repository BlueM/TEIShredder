<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;

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
	 */
	function setTheXmlId(NamedEntity $element) {
		$element->xmlid = 'my-xml-id';
		$this->assertEquals('my-xml-id', $element->xmlid);
	}

	/**
	 * @test
	 */
	function getThePersistableDataForAnEntity() {
		$entity = new NamedEntity($this->setup);
		$entity->xmlid = 'element-01';
		$entity->page = 123;
		$entity->domain = 'person';
		$entity->identifier = 'http://d-nb.info/gnd/118582143';
		$entity->contextstart = 'Painter ';
		$entity->notation = 'Michelangelo';
		$entity->contextend = ' lived in the renaissance';
		$entity->container = 'p';
		$entity->chunk = 456;
		$entity->notationhash = 'd1f9cc6d';
		$data = $entity->persistableData();
		$this->assertInternalType('array', $data);
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresAPageNumber() {
		$entity = new NamedEntity($this->setup);
		$entity->domain = 'person';
		$entity->identifier = 'http://d-nb.info/gnd/118582143';
		$entity->notation = 'Michelangelo';
		$entity->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresADomain() {
		$entity = new NamedEntity($this->setup);
		$entity->page = 123;
		$entity->identifier = 'http://d-nb.info/gnd/118582143';
		$entity->notation = 'Michelangelo';
		$entity->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresAKey() {
		$entity = new NamedEntity($this->setup);
		$entity->page = 123;
		$entity->domain = 'person';
		$entity->notation = 'Michelangelo';
		$entity->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureANamedEntityRequiresANotation() {
		$entity = new NamedEntity($this->setup);
		$entity->page = 123;
		$entity->domain = 'person';
		$entity->identifier = 'http://d-nb.info/gnd/118582143';
		$entity->persistableData();
	}

}

