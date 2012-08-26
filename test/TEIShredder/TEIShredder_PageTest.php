<?php

namespace TEIShredder;

use \TEIShredder;
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
	 * @depends createANewPage
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Page $page) {
		$page->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewPage
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage can not be set
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(Page $page) {
		$page->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewPage
	 */
	function setAPagesNumber(Page $page) {
		$page->number = 222;
		$this->assertEquals(222, $page->number);
	}

	/**
	 * @test
	 * @depends createANewPage
	 * @expectedException UnexpectedValueException
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(Page $page) {
		$page->foo;
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAPageRequiresANumber() {
		$page = new Page($this->setup);
		$page->save();
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function makeSureAPageRequiresAVolume() {
		$page = new Page($this->setup);
		$page->number = 1234;
		$page->save();
	}

}

