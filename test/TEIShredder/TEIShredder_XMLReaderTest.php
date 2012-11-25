<?php

namespace TEIShredder;

use \TEIShredder;
use \RuntimeException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_XMLReader.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class XMLReaderTest extends \PHPUnit_Framework_TestCase
{

    protected $reader;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $this->reader = new XMLReader;
    }

    /**
     * Removes the fixture
     */
    public function tearDown()
    {
        unset($this->reader);
    }

    /**
     * @test
     */
    public function getTheOpeningTagForAnEmptyNodeWithoutAttributes()
    {
        $this->reader->xml('<root><b /></root>');
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->assertSame('<b/>', $this->reader->nodeOpenString());
        }
    }

    /**
     * @test
     */
    public function getTheOpeningTagForAnEmptyNodeWithAttributesAndNamespace()
    {
        $this->reader->xml(
            '<root xmlns:abc="http://example.org/abc"><'.'b foo="bar" x="y" abc:bla="bla" /></root>'
        );
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->assertSame(
                '<'.'b foo="bar" x="y" abc:bla="bla"/>',
                $this->reader->nodeOpenString()
            );
        }
    }

    /**
     * @test
     */
    public function getTheOpeningTagForANonEmptyNodeWithoutAttributes()
    {
        $this->reader->xml('<root><b>Hallo Welt</b></root>');
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->assertSame('<b>', $this->reader->nodeOpenString());
        }
    }

    /**
     * @test
     */
    public function getTheOpeningTagForANonEmptyNodeWithAttributesAndNamespace()
    {
        $this->reader->xml(
            '<root xmlns:abc="http://example.org/abc"><'.'b foo="bar" x="y" abc:bla="bla">Hallo Welt</b></root>'
        );
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->assertSame(
                '<'.'b foo="bar" x="y" abc:bla="bla">',
                $this->reader->nodeOpenString()
            );
        }
    }

    /**
     * @test
     */
    public function checkEscapingOfSpecialCharsInAttributeValues()
    {
        $this->reader->xml('<root><ref target="http://www.example.com/?a=b&amp;c=d" /></root>');
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->assertSame(
                '<ref target="http://www.example.com/?a=b&amp;c=d"/>',
                $this->reader->nodeOpenString()
            );
        }
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function tryingToGetTheOpeningTagForAClosingTagThrowsAnException()
    {
        $this->reader->xml('<root><b></b></root>');
        while ($this->reader->read()) {
            if ('root' == $this->reader->localName or
                XMLReader::END_ELEMENT != $this->reader->nodeType
            ) {
                continue;
            }
            $this->reader->nodeOpenString();
        }
    }
}
