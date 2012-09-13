<?php

namespace TEIShredder;

use \UnexpectedValueException;
use \LogicException;
use \PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Page.
 * @package TEIShredder
 * @subpackage Tests
 */
class PageTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Setup
	 */
	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = new Setup(new PDO('sqlite::memory:'));
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
	function createANewPage() {
		$page = new Page($this->setup);
		$this->assertInstanceOf('\TEIShredder\Page', $page);
		return $page;
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAPageRequiresANumber() {
		$page = new Page($this->setup);
		$page->volume = 2;
		$page->persistableData();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAPageRequiresAVolume() {
		$page = new Page($this->setup);
		$page->number = 1234;
		$page->persistableData();
	}

	/**
	 * @test
	 */
	function getThePersistableDataOfAnObjectWithAllRequiredProperties() {
		$page = new Page($this->setup);
		$page->volume = 2;
		$page->number = 1234;
		$this->assertInternalType('array', $page->persistableData());
	}
}

