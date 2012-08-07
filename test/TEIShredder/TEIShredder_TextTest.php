<?php

namespace TEIShredder;

use \TEIShredder;
#use \RuntimeException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Text.
 * @package TEIShredder
 * @subpackage Tests
 */
class TextTest extends \PHPUnit_Framework_TestCase {

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
		$num = Text::fetchNumberOfPages($this->setup);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchTheNumberOfPagesOfTheFirstVolume() {
		$num = Text::fetchNumberOfPages($this->setup, 1);
		$this->assertInternalType('int', $num);
	}

	/**
	 * @test
	 */
	function fetchThePageNotations() {
		$notations = Text::fetchPageNotations($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid page number
	 */
	function fetchingDataForAPageByItsPagenumberThrowsAnExceptionIfThePageNotExists() {
		Text::fetchPageData($this->setup, 12345);
	}

	/**
	 * @test
	 */
	function fetchingDataForAPageByItsPagenumberWorks() {
		$data = Text::fetchPageData($this->setup, 2);
		$this->assertInternalType('array', $data);
	}

	/**
	 * @test
	 */
	function fetchTheVolumes() {
		$notations = Text::fetchVolumes($this->setup);
		$this->assertInternalType('array', $notations);
	}

	/**
	 * @test
	 */
	function fetchThePageNumberForAnElementId() {
		$pagenumber = Text::fetchPageNumberForElementId($this->setup, 'p123');
		$this->assertFalse($pagenumber);
	}

	/**
	 * @test
	 */
	function fetchNAttributesForPageNumbers() {
		$n = Text::fetchNAttributesForPageNumbers($this->setup, array(1, 2, 3));
		$this->assertInternalType('array', $n);
	}

	/**
	 * @test
	 */
	function fetchTheStructure() {
		$structure = Text::fetchStructure($this->setup, 1);
		$this->assertInternalType('array', $structure);
	}

	/**
	 * @test
	 */
	function fetchTheStructureDataForASection() {
 		$n = Text::fetchStructureDataForSection($this->setup, 5);
 		$this->assertInternalType('array', $n);
	}

}

