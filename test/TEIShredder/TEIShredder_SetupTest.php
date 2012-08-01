<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Setup.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_SetupTest extends PHPUnit_Framework_TestCase {

	/**
	 * @test
	 */
	function creatingAnObjectWithDefaultCallbacksWorks() {
		$setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:')
		);
		$this->assertInstanceOf('TEIShredder_Setup', $setup);
		return $setup;
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Plaintext conversion callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidPlaintextCallbackThrowsAnException() {
		$setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			'abc'
		);
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomPlaintextCallbacksWorks() {
		$setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			function($str) { return $str; } // Dummy plaintext conversion callback closure
		);
		$this->assertInstanceOf('TEIShredder_Setup', $setup);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Title extraction callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidTitleExtractionCallbackThrowsAnException() {
		$setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			null,
			'abc'
		);
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomTitleExtractionCallbacksWorks() {
		$setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			null,
			function($str) { return $str; } // Dummy title extraction callback closure
		);
		$this->assertInstanceOf('TEIShredder_Setup', $setup);
	}

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Unexpected member name
	 */
	function tryingToGetAnInvalidClassMemberThrowsAnException(TEIShredder_Setup $setup) {
		$setup->foobar;
	}
}

