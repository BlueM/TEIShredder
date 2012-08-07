<?php

namespace TEIShredder;

use \TEIShredder;
use \PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Setup.
 * @package TEIShredder
 * @subpackage Tests
 */
class SetupTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @test
	 */
	function creatingAnObjectWithDefaultCallbacksWorks() {
		$setup = new Setup(new PDO('sqlite::memory:'));
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Setup', $setup);
		return $setup;
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Plaintext conversion callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidPlaintextCallbackThrowsAnException() {
		new Setup(new PDO('sqlite::memory:'), '', 'abc');
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomPlaintextCallbacksWorks() {
		$setup = new Setup(
			new PDO('sqlite::memory:'),
			'',
			function($str) { return $str; } // Dummy plaintext conversion callback closure
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Setup', $setup);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Title extraction callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidTitleExtractionCallbackThrowsAnException() {
		new Setup(new PDO('sqlite::memory:'), '', null, 'abc');
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomTitleExtractionCallbacksWorks() {
		$setup = new Setup(
			new PDO('sqlite::memory:'),
			'',
			null,
			function($str) { return $str; } // Dummy title extraction callback closure
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Setup', $setup);
	}

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Unexpected member name
	 */
	function tryingToGetAnInvalidClassMemberThrowsAnException(Setup $setup) {
		$setup->foobar;
	}
}

