<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../../bootstrap.php';

/**
 * Test class for TEIShredder_Indexer_Extractor.
 * @package TEIShredder
 * @subpackage Tests
 */
class Indexer_ExtractorTest extends \PHPUnit_Framework_TestCase {

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
	function testCreatingAnExtractor() {
		$extractor = new Indexer_Extractor(
			$this->setup,
			$this->xmlreader,
			file_get_contents(TESTDIR.'/Sample-1.xml')
		);
		$this->assertInstanceOf('\\'.__NAMESPACE__.'\\Indexer_Extractor', $extractor);
		return $extractor;
	}

	/**
	 * @test
	 * @depends testCreatingAnExtractor
	 * @expectedException InvalidArgumentException
	 */
	function tryingToSetAnInvalidElementCallbackThrowsAnException(Indexer_Extractor $extractor) {
		$extractor->setElementCallback('pb', 'invalid');
	}

	/**
	 * @test
	 * @depends testCreatingAnExtractor
	 */
	function testRunningAnExtractor(Indexer_Extractor $extractor) {

		// Set an element callback for <index> tags
		$extractor->setElementCallback('index', function(XMLReader $r) {
			// For the purpose of this test, we simply return the markup-less
			// content of the tag
			return array(
				'data'=>$r->readString(),
				'attrn'=>'',
				'attrtargetend'=>'',
			);
		});

		$extractor->process();
	}

}

