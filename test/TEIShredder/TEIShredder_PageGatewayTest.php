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
		$pg = new PageGateway;
		$pg->flush($this->setup);
	}

	/**
	 * @test
	 */
	function saveANewPage() {
		$pg = new PageGateway;

		$page = new Page($this->setup);
		$page->number = 15;
		$page->xmlid = "pb-15";
		$page->rend = "normal";
		$page->n = "XV";
		$page->volume = 2;
		$page->plaintext = 'Foo';
		$pg->save($this->setup, $page);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAPageGatewayByAnUnknownPagenumberThrowsAnException() {
		$pg = new PageGateway;
		$pg->find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAPageByItsNumber() {
		$pg = new PageGateway;

		// First, create object
		$page = new Page($this->setup);
		$page->number = 20;
		$page->volume = 5;
		$pg->save($this->setup, $page);

		$obj = $pg->find($this->setup, 20);
		$this->assertInstanceOf('\TEIShredder\Page', $obj);
		$this->assertEquals(5, $page->volume);
	}

	/**
	 * @test
	 */
	function findAllPages() {

		$pg = new PageGateway;

		$page = new Page($this->setup);
		$page->number = 20;
		$page->volume = 5;
		$pg->save($this->setup, $page);

		$objs = $pg->findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(0 < count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Page', $obj);
		}
	}

}

