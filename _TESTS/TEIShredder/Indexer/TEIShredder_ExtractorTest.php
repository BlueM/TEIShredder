<?php

require_once __DIR__.'/../../bootstrap.php';

/**
 * Test class for TEIShredder_Indexer_Extractor.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_Indexer_ExtractorTest extends PHPUnit_Framework_TestCase {

	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = new TEIShredder_Setup(
			new PDO('sqlite::memory:')
		);
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
	function testCreatingAExtractor() {
		$extractor = new TEIShredder_Indexer_Extractor(
			$this->setup,
			TESTDIR.'/Sample-1.xml'
		);
		$this->assertInstanceOf('TEIShredder_Indexer_Extractor', $extractor);
	}

}

