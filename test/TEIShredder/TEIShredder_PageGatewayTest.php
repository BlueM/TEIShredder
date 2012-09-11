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
		PageGateway::flush($this->setup);
	}

	/**
	 * @test
	 */
	function saveANewPage() {
		$page = new Page($this->setup);
		$page->number = 15;
		$page->xmlid = "pb-15";
		$page->rend = "normal";
		$page->n = "XV";
		$page->volume = 2;
		$page->plaintext = 'Foo';
		$page->save();
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFetchAPageGatewayByAnUnknownPagenumberThrowsAnException() {
		PageGateway::find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAPageByItsNumber() {
		// First, create object
		$page = new Page($this->setup);
		$page->number = 20;
		$page->volume = 5;
		$page->save();

		$obj = PageGateway::find($this->setup, 20);
		$this->assertInstanceOf('\TEIShredder\Page', $obj);
		$this->assertEquals(5, $page->volume);
	}

	/**
	 * @test
	 */
	function findAllPages() {

		$page = new Page($this->setup);
		$page->number = 20;
		$page->volume = 5;
		$page->save();

		$objs = PageGateway::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(0 < count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\Page', $obj);
		}
	}

}

