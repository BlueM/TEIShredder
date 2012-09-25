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
	 * @var NamedEntityGateway
	 */
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$setup = prepare_default_data();
		$this->obj = new NamedEntityGateway($setup->database, $setup->factory, $setup->prefix);
	}

	/**
	 * @test
	 */
	function flushTheData() {
		$this->obj->flush();
		$objs = $this->obj->find();
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveANamedEntity() {

		$this->obj->flush();

		$ent = new NamedEntity();
		$ent->xmlid = 'entity-123';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'demo';
		$ent->identifier= '4711';
		$ent->notation = 'Named Entity';
		$this->obj->save($ent);

		$objs = $this->obj->find();
		$this->assertInternalType('array', $objs);
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[0]);
	}

	/**
	 * @test
	 */
	function findAllNamedEntities() {

		$this->obj->flush();

		$ent = new NamedEntity();
		$ent->xmlid = 'entity-1';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'person';
		$ent->identifier= 44;
		$ent->notation = 'Named Entity 1';
		$this->obj->save($ent);

		$ent = new NamedEntity;
		$ent->xmlid = 'entity-123';
		$ent->page = 55;
		$ent->chunk = 66;
		$ent->domain = 'person';
		$ent->identifier= 77;
		$ent->notation = 'Named Entity 2';
		$this->obj->save($ent);

		$objs = $this->obj->find();
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[0]);
		$this->assertSame('Named Entity 1', $objs[0]->notation);
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[1]);
		$this->assertSame('Named Entity 2', $objs[1]->notation);
	}

	/**
	 * @test
	 */
	function findEntitiesBySearchCriteria() {

		$ent = new NamedEntity();
		$ent->xmlid = 'e1';
		$ent->page = 22;
		$ent->chunk = 33;
		$ent->domain = 'person';
		$ent->identifier = 44;
		$ent->notation = 'Entity 1';
		$this->obj->save($ent);

		$ent = new NamedEntity;
		$ent->xmlid = 'e2';
		$ent->page = 55;
		$ent->chunk = 66;
		$ent->domain = 'place';
		$ent->identifier = 44;
		$ent->notation = 'Entity 2';
		$this->obj->save($ent);

		$objs = $this->obj->find();
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));

		$objs = $this->obj->find('domain = person');
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));

		$objs = $this->obj->find('domain == artwork');
		$this->assertInternalType('array', $objs);
		$this->assertTrue(0 == count($objs));

		$objs = $this->obj->find('identifier != 19');
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));

		$objs = $this->obj->find('identifier != 19');
		$this->assertInternalType('array', $objs);
		$this->assertTrue(2 == count($objs));

		$objs = $this->obj->find('page >= 22', ' chunk<>66');
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));
		$this->assertSame('Entity 1', $objs[0]->notation);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToFindANamedEntityByAnInvalidPropertyThrowsAnException() {
		$this->obj->find('invalid = 1');
	}

}

