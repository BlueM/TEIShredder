<?php

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLReader.
 * @package TEIShredder
 * @subpackage Tests
 */
class TEIShredder_XMLReaderTest extends PHPUnit_Framework_TestCase {

	var $reader;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->reader = new TEIShredder_XMLReader;
	}

	/**
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->reader);
	}

	/**
	 * @test
	 */
	function getTheOpeningTagForAnEmptyNodeWithoutAttributes() {
		$this->reader->xml('<root><b /></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('<b/>', $this->reader->nodeOpenString());
		}
	}

	/**
	 * @test
	 */
	function getTheOpeningTagForAnEmptyNodeWithAttributesAndNamespace() {
		$this->reader->xml('<root xmlns:abc="http://example.org/abc"><b foo="bar" x="y" abc:bla="bla" /></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('<b foo="bar" x="y" abc:bla="bla"/>', $this->reader->nodeOpenString());
		}
	}

	/**
	 * @test
	 */
	function getTheOpeningTagForANonEmptyNodeWithoutAttributes() {
		$this->reader->xml('<root><b>Hallo Welt</b></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('<b>', $this->reader->nodeOpenString());
		}
	}

	/**
	 * @test
	 */
	function getTheOpeningTagForANonEmptyNodeWithAttributesAndNamespace() {
		$this->reader->xml('<root xmlns:abc="http://example.org/abc"><b foo="bar" x="y" abc:bla="bla">Hallo Welt</b></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('<b foo="bar" x="y" abc:bla="bla">', $this->reader->nodeOpenString());
		}
	}

	/**
	 * @test
	 */
	function checkEscapingOfSpecialCharsInAttributeValues() {
		$this->reader->xml('<root><ref target="http://www.example.com/?a=b&amp;c=d" /></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('<ref target="http://www.example.com/?a=b&amp;c=d"/>', $this->reader->nodeOpenString());
		}
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	function tryingToGetTheOpeningTagForAClosingTagThrowsAnException() {
		$this->reader->xml('<root><b></b></root>');
		while ($this->reader->read()) {
			if ('root' == $this->reader->localName or
			    XMLReader::END_ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->reader->nodeOpenString();
		}
	}

}

