<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_PageGateway.
 * @package TEIShredder
 * @subpackage Tests
 */
class PageGatewayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PageGateway
	 */
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$setup = prepare_default_data();
		$this->obj = new PageGateway($setup->database, $setup->factory, $setup->prefix);
	}

	/**
	 * @test
	 */
	function flushTheData() {
		$this->obj->flush();
	}

	/**
	 * @test
	 */
	function saveANewPage() {
		$page = new Page;
		$page->number = 15;
		$page->xmlid = "pb-15";
		$page->rend = "normal";
		$page->n = "XV";
		$page->volume = 2;
		$page->plaintext = 'Foo';
		$this->obj->save($page);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAPageGatewayByAnUnknownPagenumberThrowsAnException() {
		$this->obj->find(9999999);
	}

	/**
	 * @test
	 */
	function findAPageByItsNumber() {

		// First, create object
		$page = new Page;
		$page->number = 20;
		$page->volume = 5;
		$this->obj->save($page);

		$obj = $this->obj->find(20);
		$this->assertInstanceOf('\TEIShredder\Page', $obj);
		$this->assertEquals(5, $page->volume);
	}

	/**
	 * @test
	 */
	function findAllPages() {
		$page = new Page;
		$page->number = 20;
		$page->volume = 5;
		$this->obj->save($page);

		$objs = $this->obj->findAll();
		$this->assertInternalType('array', $objs);
		$this->assertTrue(0 < count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Page', $obj);
		}
	}

}

