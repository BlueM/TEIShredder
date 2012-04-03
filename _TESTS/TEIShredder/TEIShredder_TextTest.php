<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Text.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_TextTest extends PHPUnit_Framework_TestCase {

	static $setup;

	/**
	 * Initialization that is done once for the whole class.
	 */
	public static function setUpBeforeClass() {
		static::$setup = prepare_default_data();
	}

	/**
	 * @test
	 */
	function fetchTheStructure() {
		$structure = TEIShredder_Text::fetchStructure(static::$setup, 1);
		$this->assertInternalType('array', $structure);
	}

	/**
	 * @test
	 */
	function fetchTheNumberOfPages() {
		$num = TEIShredder_Text::fetchNumberOfPages(static::$setup);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchThePageNotations() {
		$notations = TEIShredder_Text::fetchPageNotations(static::$setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 */
	function fetchTheVolumes() {
		$notations = TEIShredder_Text::fetchVolumes(static::$setup);
		$this->assertInternalType('array', $notations);
	}

}

