<?php

namespace TEIShredder;

use \TEIShredder;
use \RuntimeException;

require_once __DIR__.'/../../bootstrap.php';

/**
 * Test class for TEIShredder_Indexer_Chunker.
 * @package TEIShredder
 * @subpackage Tests
 */
class Indexer_ChunkerTest extends \PHPUnit_Framework_TestCase {

	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
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
	function testCreatingAChunker() {

		$chunker = new Indexer_Chunker(
			$this->setup,
			file_get_contents(TESTDIR.'/Sample-1.xml')
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Indexer_Chunker', $chunker);
		return $chunker;
	}

	/**
	 * @test
	 * @depends testCreatingAChunker
	 */
	function testRunningAChunker(Indexer_Chunker $chunker) {
		$chunker->process();
	}

}

