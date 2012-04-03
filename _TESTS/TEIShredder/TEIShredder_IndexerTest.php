<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Indexer.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_IndexerTest extends PHPUnit_Framework_TestCase {

	var $reader;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->reader = new TEIShredder_Indexer;
	}

	/**
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->reader);
	}


}

