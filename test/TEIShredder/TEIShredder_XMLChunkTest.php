<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLChunk.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_XMLChunkTest extends PHPUnit_Framework_TestCase {

	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
		$chunker = new TEIShredder_Indexer_Chunker(
			$this->setup,
			file_get_contents(TESTDIR.'/Sample-1.xml')
		);
		$chunker->process();
	}

	/**
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->setup);
	}

	/**
	 * @test
	 * @return TEIShredder_XMLChunk
	 */
	function getTheChunksForPage2() {
		$chunks = TEIShredder_XMLChunk::fetchObjectsByPageNumber($this->setup, 2);
		$this->assertInternalType('array', $chunks);
		$this->assertSame(1, count($chunks));
		$this->assertInstanceOf('TEIShredder_XMLChunk', $chunks[0]);
		return $chunks[0];
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheIdForAChunk(TEIShredder_XMLChunk $chunk) {
		$this->assertSame(7, $chunk->getId());
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksWellformedXml(TEIShredder_XMLChunk $chunk) {
		$xml = $chunk->getWellFormedXML();
		$xml = new SimpleXMLElement($xml);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksXml(TEIShredder_XMLChunk $chunk) {
		$xml = $chunk->getXML();
		$this->assertInternalType('string', $xml);
		// Make sure the XML is really part of the well-formed XML
		$this->assertTrue(0 < strpos($chunk->getWellFormedXML(), $chunk->getXML()));
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksPlaintext(TEIShredder_XMLChunk $chunk) {
		$text = $chunk->getPlaintext();
		$this->assertInternalType('string', $text);
		$this->assertFalse(strpos($text, '<'));
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksSection(TEIShredder_XMLChunk $chunk) {
		$section = $chunk->getSection();
		$this->assertSame(4, $section);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksColumn(TEIShredder_XMLChunk $chunk) {
		$section = $chunk->getColumn();
		$this->assertInternalType('string', $section);
		$this->assertSame('', $section);
	}

}

