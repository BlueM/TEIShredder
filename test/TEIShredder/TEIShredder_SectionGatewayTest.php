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
	 * @var SectionGateway
	 */
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$setup = prepare_default_data();
		$this->obj = new SectionGateway($setup->database, $setup->factory, $setup->prefix);
	}

	/**
	 * @test
	 */
	function flushTheData() {
		$this->obj->flush();
		$objs = $this->obj->find();
		$this->assertTrue(0 == count($objs));
	}

//
//	/**
//	 * @test
//	 */
//	function filterData() {
//
//		$where = $this->obj->find();
//		$this->assertEquals(1, $where);
//
//		$where = $this->obj->find(array('id < 299'));
//		$this->assertEquals("1 AND id < '299'", $where);
//
//		$where = $this->obj->find(array('id > 301'));
//		$this->assertEquals("1 AND id > '301'", $where);
//
//		$where = $this->obj->find(array('title != '));
//		$this->assertEquals("1 AND title <> ''", $where);
//
//		$where = $this->obj->find(array('id<>5'));
//		$this->assertEquals("1 AND id <> '5'", $where);
//
//		$where = $this->obj->find(array('title=abc'));
//		$this->assertEquals("1 AND title = 'abc'", $where);
//
//		$where = $this->obj->find(array('title==abc'));
//		$this->assertEquals("1 AND title = 'abc'", $where);
//
//		$where = $this->obj->find(array('title ~ foo'));
//		$this->assertEquals("1 AND title LIKE 'foo'", $where);
//
//		$where = $this->obj->find(array('title ~ %foo%'));
//		$this->assertEquals("1 AND title LIKE '%foo%'", $where);
//	}

	/**
	 * @test
	 */
	function saveASection() {
		$this->obj->flush();

		$section = new Section;
		$section->id = 5;
		$section->volume = 3;
		$section->title = "Section 5";
		$section->page = 55;
		$section->level = 1;
		$section->element = 'div';
		$this->obj->save($section);

		$obj = $this->obj->findByIdentifier(5);
		$this->assertInstanceOf('\TEIShredder\Section', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchASectionGatewayByAnUnknownSectionGatewaynumberThrowsAnException() {
		$this->obj->findByIdentifier(9999999);
	}

	/**
	 * @test
	 */
	function findASectionByItsId() {

		$this->obj->flush();

		// First, create object
		$section = new Section();
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		$this->obj->save($section);

		$obj = $this->obj->findByIdentifier(17);
		$this->assertInstanceOf('\TEIShredder\Section', $obj);
		$this->assertEquals("Chapter 17", $section->title);
	}

	/**
	 * @test
	 */
	function findAllSections() {

		$this->obj->flush();

		$section = new Section();
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		$this->obj->save($section);

		$section = new Section();
		$section->id = 23;
		$section->volume = 2;
		$section->title = "Chapter 23";
		$section->page = 180;
		$section->level = 2;
		$section->element = 'div';
		$this->obj->save($section);

		$objs = $this->obj->find();
		$this->assertInternalType('array', $objs);
		$this->assertSame(2, count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Section', $obj);
		}
	}

	/**
	 * @test
	 */
	function findAllSectionThatMatchCertainCriteria() {

		$this->obj->flush();

		$section = new Section();
		$section->id = 17;
		$section->volume = 1;
		$section->title = "Chapter 17";
		$section->page= 17;
		$section->level = 3;
		$section->element = 'div';
		$this->obj->save($section);

		$section = new Section();
		$section->id = 23;
		$section->volume = 2;
		$section->title = "Chapter 23";
		$section->page = 180;
		$section->level = 2;
		$section->element = 'div';
		$this->obj->save($section);

		$objs = $this->obj->find('id = 23');
		$this->assertInternalType('array', $objs);
		$this->assertSame(1, count($objs));
		$this->assertEquals(2, $objs[0]->volume);

		$objs = $this->obj->find('title = Chapter');
		$this->assertInternalType('array', $objs);
		$this->assertSame(0, count($objs));

		$objs = $this->obj->find('title~ Chapter%');
		$this->assertInternalType('array', $objs);
		$this->assertSame(2, count($objs));

		$objs = $this->obj->find('page >= 17');
		$this->assertInternalType('array', $objs);
		$this->assertSame(2, count($objs));
	}

}

