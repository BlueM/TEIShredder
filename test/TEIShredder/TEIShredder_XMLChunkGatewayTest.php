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
	 * @var XMLChunkGateway
	 */
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$setup = prepare_default_data();
		$this->obj = new XMLChunkGateway($setup->database, $setup->factory, $setup->prefix);
	}

	/**
	 * @test
	 */
	function saveAnXMLChunk() {
		$this->obj->flush();

		$chunk = new XMLChunk();
		$chunk->page = 3;
		$chunk->section = 4;
		$chunk->xml = '<foot/>';
		$this->obj->save($chunk);

		$chunks = $this->obj->findByPageNumber(3);
		$this->assertInternalType('array', $chunks);
		$this->assertInstanceOf('\TEIShredder\XMLChunk', $chunks[0]);
	}

	/**
	 * @test
	 * @todo Add assertion
	 */
	function flushTheData() {
		$this->obj->flush();
	}


}

