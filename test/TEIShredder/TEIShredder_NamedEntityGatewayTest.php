<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for NamedEntityGateway
 * @package TEIShredder
 * @subpackage Tests
 */
class NamedEntityGatewayTest extends \PHPUnit_Framework_TestCase {

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
		$neg = new NamedEntityGateway;
		$neg->flush($this->setup);
		$objs = $neg->findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveANamedEntity() {

		$neg = new NamedEntityGateway;

		$neg->flush($this->setup);

		$ent = new NamedEntity($this->setup);
		$ent->xmlid = 'entity-123';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'demo';
		$ent->identifier= '4711';
		$ent->notation = 'Named Entity';
		$neg->save($this->setup, $ent);

		$obj = $neg->find($this->setup, 'entity-123');
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchANamedEntityByAnUnknownIdentifierThrowsAnException() {
		$neg = new NamedEntityGateway;
		$neg->find($this->setup, 9999999);
	}

	/**
	 * @test
	 * @todo Will have to be changed
	 */
	function findANamedEntityByItsXmlId() {

		$neg = new NamedEntityGateway;

		$neg->flush($this->setup);

		$ent = new NamedEntity($this->setup);
		$ent->xmlid = 'entity-123';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'demo';
		$ent->identifier= '4711';
		$ent->notation = 'Named Entity';
		$neg->save($this->setup, $ent);

		$obj = $neg->find($this->setup, 'entity-123');
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $obj);
		$this->assertEquals("Named Entity", $ent->notation);
	}

	/**
	 * @test
	 */
	function findAllNamedEntitys() {

		$neg = new NamedEntityGateway;

		$neg->flush($this->setup);

		$ent = new NamedEntity($this->setup);
		$ent->xmlid = 'entity-1';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'person';
		$ent->identifier= 44;
		$ent->notation = 'Named Entity 1';
		$neg->save($this->setup, $ent);

		$ent = new NamedEntity($this->setup);
		$ent->xmlid = 'entity-123';
		$ent->page = 55;
		$ent->chunk = 66;
		$ent->domain = 'person';
		$ent->identifier= 77;
		$ent->notation = 'Named Entity 2';
		$neg->save($this->setup, $ent);

		$objs = $neg->findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[0]);
		$this->assertSame('Named Entity 1', $objs[0]->notation);
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[1]);
		$this->assertSame('Named Entity 2', $objs[1]->notation);
	}

}

