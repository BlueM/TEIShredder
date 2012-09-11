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
	var $xmlreader;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = prepare_default_data();
		$this->xmlreader = new XMLReader;
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
			$this->xmlreader,
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

	/**
	 * @test
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Multiple <titlePart>
	 */
	function makeSureAChunkerThrowsAnExceptionIfThereAreSeveralTitlesForAVolume() {

		$xml = <<<_XML_
<TEI xmlns="http://www.tei-c.org/ns/1.0">
  <teiHeader>
    <fileDesc>
      <titleStmt><title>...</title></titleStmt>
      <publicationStmt><p>...</p></publicationStmt>
      <sourceDesc><p>...</p></sourceDesc>
    </fileDesc>
  </teiHeader>
  <text>
    <front>
  		<titlePart>Title 1</titlePart>
  		<titlePart>Title 2</titlePart>
  	</front>
    <body>
      <p>...</p>
    </body>
  </text>
</TEI>
_XML_;

		$chunker = new Indexer_Chunker(
			$this->setup,
			$this->xmlreader,
			$xml
		);

		$chunker->process();
	}

	/**
	 * @test
	 */
	function runAChunkerWithTextbeforepbSetToOff() {

		$xml = <<<_XML_
<TEI xmlns="http://www.tei-c.org/ns/1.0">
  <teiHeader>
    <fileDesc>
      <titleStmt><title>...</title></titleStmt>
      <publicationStmt><p>...</p></publicationStmt>
      <sourceDesc><p>...</p></sourceDesc>
    </fileDesc>
  </teiHeader>
  <group>

  <pb n="1" />
  <text>
    <titlePart>Vol1</titlePart>
    <body>
      <p>...</p>
    </body>
  </text>

  <pb n="2" />
  <text>
    <titlePart>Vol2</titlePart>
    <body>
      <p>...</p>
    </body>
  </text>
  </group>
</TEI>
_XML_;

		$chunker = new Indexer_Chunker(
			$this->setup,
			$this->xmlreader,
			$xml
		);
		$chunker->textBeforePb = false;
		$chunker->process();

		$volumes = VolumeGateway::findAll($this->setup);

		$this->assertEquals(1, $volumes[0]->number);
		$this->assertEquals(2, $volumes[1]->number);
	}
}

