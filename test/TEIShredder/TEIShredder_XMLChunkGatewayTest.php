<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLChunkGateway
 * @package TEIShredder
 * @subpackage Tests
 */
class XMLChunkGatewayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Setup
	 */
	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
	}

	/**
	 * @test
	 */
	function saveAnXMLChunk() {
		XMLChunkGateway::flush($this->setup);

		$chunk = new XMLChunk($this->setup);
		$chunk->page = 3;
		$chunk->section = 4;
		$chunk->xml = '<foot/>';
		XMLChunkGateway::save($this->setup, $chunk);

		$chunks = XMLChunkGateway::findByPageNumber($this->setup, 3);
		$this->assertInternalType('array', $chunks);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $chunks[0]);
	}

	/**
	 * @test
	 * @todo Add assertion
	 */
	function flushTheData() {
		XMLChunkGateway::flush($this->setup);
	}


}

