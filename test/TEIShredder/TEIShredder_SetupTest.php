<?php

namespace TEIShredder;

use \TEIShredder;
use \PDO;
use \UnexpectedValueException;
use \InvalidArgumentException;

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
		new Setup(new PDO('sqlite::memory:'), null, '', 'abc');
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomPlaintextCallbacksWorks() {
		$setup = new Setup(
			new PDO('sqlite::memory:'),
			null,
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
		new Setup(new PDO('sqlite::memory:'), null, '', null, 'abc');
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithACustomTitleExtractionCallbacksWorks() {
		$setup = new Setup(
			new PDO('sqlite::memory:'),
			null,
			'',
			null,
			function($str) { return $str; } // Dummy title extraction callback closure
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Setup', $setup);
	}

	/**
	 * @test
	 */
	function creatingAnObjectWithADifferentFactoryWorks() {
		$factory = $this->getMock('\TEIShredder\FactoryInterface');
		$setup = new Setup(
			new PDO('sqlite::memory:'),
			$factory,
			'',
			null,
			function($str) { return $str; } // Dummy title extraction callback closure
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Setup', $setup);
		$this->assertInstanceOf('\TEIShredder\FactoryInterface', $setup->factory);
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

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnInvalidClassMemberThrowsAnException(Setup $setup) {
		$setup->foo = 'bar';
	}

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnUnsettableClassMemberThrowsAnException(Setup $setup) {
		$setup->database = 'Bla';
	}

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 * @expectedException InvalidArgumentException
	 */
	function tryingToSetTheChunktagsToANonArrayThrowsAnException(Setup $setup) {
		$setup->chunktags = 'Not an array';
	}

	/**
	 * @test
	 * @depends creatingAnObjectWithDefaultCallbacksWorks
	 */
	function tryingToSetTheChunktagsToAnArrayWorks(Setup $setup) {
		$array = array('text', 'pb');
		$setup->chunktags = $array;
		$this->assertSame($array, $setup->chunktags);
	}
}

