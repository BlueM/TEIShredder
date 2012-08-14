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
	 */
	function createANewChunk() {
		$chunk = new XMLChunk($this->setup);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $chunk);
		return $chunk;
	}

	/**
	 * @test
	 * @depends createANewChunk
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(XMLChunk $chunk) {
		$chunk->foo= 'bar';
	}

	/**
	 * @test
	 * @depends createANewChunk
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage can not be set
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(XMLChunk $chunk) {
		$chunk->_setup = 'something';
	}

	/**
	 * @test
	 * @depends createANewChunk
	 */
	function setAChunksId(XMLChunk $chunk) {
		$chunk->id = 12345;
		$this->assertEquals(12345, $chunk->id);
	}

	/**
	 * @test
	 * @depends createANewChunk
	 * @expectedException UnexpectedValueException
	 * @expectedExceptionMessage Invalid property
	 */
	function tryingToGetAnInvalidPropertyThrowsAnException(XMLChunk $chunk) {
		$chunk->foo;
	}

	/**
	 * @test
	 * @depends createANewChunk
	 */
	function getAChunksId(XMLChunk $chunk) {
		$this->assertEquals(12345, $chunk->id);
	}

	/**
	 * @test
	 * @depends createANewChunk
	 */
	function getAChunksWellformedXML(XMLChunk $chunk) {
		$chunk->prestack = '<text><p>';
		$chunk->xml = 'Hello world</p>';
		$chunk->poststack = '</text>';
		$this->assertEquals($chunk->prestack.$chunk->xml.$chunk->poststack, $chunk->getWellFormedXML());
	}

	/**
	 * @test
	 */
	function saveANewChunk() {
		$chunk = new XMLChunk($this->setup);
		$chunk->id = 123;
		$chunk->page = 17;
		$chunk->section = 5;
		$chunk->prestack = '<p>';
		$chunk->xml = 'Foo</p>';
		$chunk->plaintext = 'Foo';
		$chunk->save();
	}

		/**
	 * @test
	 * @return XMLChunk
	 */
	function getTheChunksForPage2() {
		// Fill database with example data
		$chunker = new Indexer_Chunker(
			$this->setup,
			new XMLReader,
			file_get_contents(TESTDIR.'/Sample-1.xml')
		);
		$chunker->process();

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
		$this->assertEquals('', $chunk->column);
	}


}



