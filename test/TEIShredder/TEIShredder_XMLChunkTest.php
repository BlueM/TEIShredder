<?php

namespace TEIShredder;

use \TEIShredder;
use \SimpleXMLElement;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLChunk.
 * @package TEIShredder
 * @subpackage Tests
 */
class XMLChunkTest extends \PHPUnit_Framework_TestCase {

	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
		$chunker = new Indexer_Chunker(
			$this->setup,
			new XMLReader,
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
	 * @return XMLChunk
	 */
	function getTheChunksForPage2() {
		$chunks = XMLChunk::fetchObjectsByPageNumber($this->setup, 2);
		$this->assertInternalType('array', $chunks);
		$this->assertSame(1, count($chunks));
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\XMLChunk', $chunks[0]);
		return $chunks[0];
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheIdForAChunk(XMLChunk $chunk) {
		$this->assertEquals(9, $chunk->id);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksWellformedXml(XMLChunk $chunk) {
		$xml = $chunk->getWellFormedXML();
		new SimpleXMLElement($xml);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksXml(XMLChunk $chunk) {
		$xml = $chunk->xml;
		$this->assertInternalType('string', $xml);
		// Make sure the XML is really part of the well-formed XML
		$this->assertTrue(false !== strpos($chunk->getWellFormedXML(), $xml));
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksPlaintext(XMLChunk $chunk) {
		$text = $chunk->plaintext;
		$this->assertInternalType('string', $text);
		$this->assertFalse(strpos($text, '<'));
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksSection(XMLChunk $chunk) {
		$this->assertEquals(5, $chunk->section);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 */
	function getTheChunksColumn(XMLChunk $chunk) {
		$section = $chunk->column;
		$this->assertInternalType('string', $section);
		$this->assertSame('', $section);
	}

	/**
	 * @test
	 * @depends getTheChunksForPage2
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Unexpected
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(XMLChunk $chunk) {
		$chunk->inexistent_property;
	}
}

