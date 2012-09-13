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
	function flushTheData() {
		XMLChunkGateway::flush($this->setup);
		$objs = XMLChunkGateway::findAll($this->setup);
		$this->assertTrue(0 == count($objs));
	}

	/**
	 * @test
	 */
	function saveAXMLChunk() {
		XMLChunkGateway::flush($this->setup);

		$volume = new XMLChunk($this->setup);
		$volume->number = 3;
		$volume->title = "Hello world";
		$volume->pagenumber = 123;
		XMLChunkGateway::save($this->setup, $volume);

		$obj = XMLChunkGateway::find($this->setup, 3);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $obj);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToFindAXMLChunkByAnUnknownXMLChunkNumberThrowsAnException() {
		XMLChunkGateway::find($this->setup, 9999999);
	}

	/**
	 * @test
	 */
	function findAXMLChunkByItsNumber() {

		XMLChunkGateway::flush($this->setup);

		// First, create object
		$volume = new XMLChunk($this->setup);
		$volume->number = 17;
		$volume->title = "XMLChunk 17";
		$volume->pagenumber = 17;
		XMLChunkGateway::save($this->setup, $volume);

		$obj = XMLChunkGateway::find($this->setup, 17);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $obj);
		$this->assertEquals("XMLChunk 17", $volume->title);
	}

	/**
	 * @test
	 */
	function findAllXMLChunks() {

		XMLChunkGateway::flush($this->setup);

		$volume = new XMLChunk($this->setup);
		$volume->number = 20;
		$volume->title = "XMLChunk 20";
		$volume->pagenumber = 20;
		XMLChunkGateway::save($this->setup, $volume);

		$objs = XMLChunkGateway::findAll($this->setup);
		$this->assertInternalType('array', $objs);
		$this->assertTrue(1 == count($objs));
		foreach ($objs as $obj) {
			$this->assertInstanceOf('\TEIShredder\XMLChunk', $obj);
		}
	}

}

