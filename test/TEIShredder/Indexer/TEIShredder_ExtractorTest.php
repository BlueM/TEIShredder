<?php

require_once __DIR__.'/../../bootstrap.php';

/**
 * Test class for TEIShredder_Indexer_Extractor.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_Indexer_ExtractorTest extends PHPUnit_Framework_TestCase {

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
	function testCreatingAnExtractor() {
		$extractor = new TEIShredder_Indexer_Extractor(
			$this->setup,
			file_get_contents(TESTDIR.'/Sample-1.xml')
		);
		$this->assertInstanceOf('TEIShredder_Indexer_Extractor', $extractor);
		return $extractor;
	}

	/**
	 * @test
	 * @depends testCreatingAnExtractor
	 * @expectedException InvalidArgumentException
	 */
	function tryingToSetAnInvalidElementCallbackThrowsAnException(TEIShredder_Indexer_Extractor $extractor) {
		$extractor->setElementCallback('pb', 'invalid');
	}

	/**
	 * @test
	 * @depends testCreatingAnExtractor
	 */
	function testRunningAnExtractor(TEIShredder_Indexer_Extractor $extractor) {

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

