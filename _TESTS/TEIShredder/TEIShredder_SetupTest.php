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
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Plaintext conversion callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidPlaintextCallbackThrowsAnException() {
		$tshrs = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			'abc'
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Title extraction callback is invalid
	 */
	function tryingToCreateAnObjectWithAnInvalidTitleExtractionCallbackThrowsAnException() {
		$tshrs = new TEIShredder_Setup(
			new PDO('sqlite::memory:'),
			'',
			null,
			'abc'
		);
	}

	/**
	 * @test
	 */
	function tryingToCreateAnObjectWithDefaultCallbacksWorks() {
		$tshrs = new TEIShredder_Setup(
			new PDO('sqlite::memory:')
		);
		$this->assertInstanceOf('TEIShredder_Setup', $tshrs);
	}

}

