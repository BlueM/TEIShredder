<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLChunk.
 * @package TEIShredder
 * @subpackage Tests
 */
class XMLChunkTest extends \PHPUnit_Framework_TestCase {

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
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->setup);
	}

	/**
	 * @test
	 * @return XMLChunk
	 */
	function createANewChunk() {
		$chunk = new XMLChunk($this->setup);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $chunk);
		return $chunk;
	}

	/**
	 * @test
	 */
	function getAChunksWellformedXML() {
		$chunk = new XMLChunk($this->setup);
		$chunk->prestack = '<text><p>';
		$chunk->xml = 'Hello world</p>';
		$chunk->poststack = '</text>';
		$this->assertEquals($chunk->prestack.$chunk->xml.$chunk->poststack, $chunk->getWellFormedXML());
	}

	/**
	 * @test
	 * @expectedException LogicException
	 */
	function tryingToGetPersistableDataForAnObjectWithIncompleteDataThrowsAnException() {
		$volume = new XMLChunk($this->setup);
		$volume->persistableData();
	}

	/**
	 * @test
	 */
	function gettingThePersistableDataForAnObjectWithCompleteDataSucceeds() {
		$chunk = new XMLChunk($this->setup);
		$chunk->page = 15;
		$chunk->section = 37;
		$data = $chunk->persistableData();
		$this->assertInternalType('array', $data);
		$this->assertSame(15, $chunk->page);
		$this->assertSame(37, $chunk->section);
	}

}



