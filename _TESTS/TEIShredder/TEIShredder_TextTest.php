<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Text.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_TextTest extends PHPUnit_Framework_TestCase {

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
	 */
	function fetchTheStructure() {
		$structure = TEIShredder_Text::fetchStructure($this->setup, 1);
		$this->assertInternalType('array', $structure);
	}

	/**
	 * @test
	 */
	function fetchTheNumberOfPages() {
		$num = TEIShredder_Text::fetchNumberOfPages($this->setup);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchThePageNotations() {
		$notations = TEIShredder_Text::fetchPageNotations($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid page number
	 */
	function fetchingDataForAPageByItsPagenumberThrowsAnExceptionIfThePageNotExists() {
		TEIShredder_Text::fetchPageData($this->setup, 12345);
	}

	/**
	 * @test
	 */
	function fetchTheVolumes() {
		$notations = TEIShredder_Text::fetchVolumes($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 */
	function fetchThePageNumberForAnElementId() {
		$pagenumber = TEIShredder_Text::fetchPageNumberForElementId($this->setup, 'p123');
		$this->assertFalse($pagenumber);
	}

	/**
	 * @test
	 */
	function fetchNAttributesForPageNumbers() {
		$n = TEIShredder_Text::fetchNAttributesForPageNumbers($this->setup, array(1, 2, 3));
		$this->assertInternalType('array', $n);
	}


	/**
	 * @test
	 */
	function fetchTheStructureDataForASection() {
// 		$n = TEIShredder_Text::fetchStructureDataForSection($this->setup, 1);
// 		$this->assertInternalType('array', $n);
	}

}

