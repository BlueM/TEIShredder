<?php

namespace TEIShredder;

use \TEIShredder;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_DocumentInfo.
 * @package TEIShredder
 * @subpackage Tests
 */
class DocumentInfoTest extends \PHPUnit_Framework_TestCase {

	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {

		$this->setup = prepare_default_data();

		$chunker = new Indexer_Chunker(
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
	function fetchTheNumberOfPages() {
		$num = DocumentInfo::fetchNumberOfPages($this->setup);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchTheNumberOfPagesOfTheFirstVolume() {
		$num = DocumentInfo::fetchNumberOfPages($this->setup, 1);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchThePageNotations() {
		$notations = DocumentInfo::fetchPageNotations($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid page number
	 */
	function fetchingDataForAPageByItsPagenumberThrowsAnExceptionIfThePageNotExists() {
		DocumentInfo::fetchPageData($this->setup, 12345);
	}

	/**
	 * @test
	 */
	function fetchingDataForAPageByItsPagenumberWorks() {
		$data = DocumentInfo::fetchPageData($this->setup, 2);
		$this->assertInternalType('array', $data);
	}

	/**
	 * @test
	 */
	function fetchTheVolumes() {
		$notations = DocumentInfo::fetchVolumes($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 */
	function fetchThePageNumberForAnElementId() {
		$pagenumber = DocumentInfo::fetchPageNumberForElementId($this->setup, 'p123');
		$this->assertFalse($pagenumber);
	}

	/**
	 * @test
	 */
	function fetchNAttributesForPageNumbers() {
		$n = DocumentInfo::fetchNAttributesForPageNumbers($this->setup, array(1, 2, 3));
		$this->assertInternalType('array', $n);
	}

	/**
	 * @test
	 */
	function fetchingTheStructureReturnsAnArray() {
		$structure = DocumentInfo::fetchStructure($this->setup, 1);
		$this->assertInternalType('array', $structure);
	}

	/**
	 * @test
	 */
	function forASectionWithSubsectionsTheChildrenFlagIsTrue() {
		$structure = DocumentInfo::fetchStructure($this->setup, 1);
		$this->assertTrue($structure[3]['children']);
	}

	/**
	 * @test
	 */
	function forASectionWithoutSubsectionsTheChildrenFlagIsFalse() {
		$structure = DocumentInfo::fetchStructure($this->setup, 1);
		$this->assertFalse($structure[4]['children']);
	}

	/**
	 * @test
	 */
	function escapedSpecialCharactersInSectionTitlesAreUnescaped() {
		$structure = DocumentInfo::fetchStructure($this->setup, 1);
		$this->assertSame('Handling of <sic>, <del> etc.', $structure[7]['title']);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid section ID
	 */
	function tryingTofetchTheStructureDataForAnInvalidSectionThrowsAnException() {
 		DocumentInfo::fetchStructureDataForSection($this->setup, 9999);
	}

	/**
	 * @test
	 */
	function fetchTheStructureDataForASection() {
 		$n = DocumentInfo::fetchStructureDataForSection($this->setup, 5);
 		$this->assertInternalType('array', $n);
	}

}

