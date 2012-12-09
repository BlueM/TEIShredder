<?php

namespace TEIShredder;

use LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\XMLChunk.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\XMLChunk
 */
class XMLChunkTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Setup
     */
    protected $setup;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $this->setup = new Setup($pdoMock);
    }

    /**
     * Removes the fixture
     */
    public function tearDown()
    {
        unset($this->setup);
    }

    /**
     * @test
     * @return XMLChunk
     */
    public function createANewChunk()
    {
        $chunk = new XMLChunk($this->setup);
        $this->assertInstanceOf('TEIShredder\XMLChunk', $chunk);
        return $chunk;
    }

    /**
     * @test
     */
    public function getAChunksWellformedXML()
    {
        $chunk            = new XMLChunk($this->setup);
        $chunk->prestack  = '<text><p>';
        $chunk->xml       = 'Hello world</p>';
        $chunk->poststack = '</text>';
        $this->assertEquals(
            $chunk->prestack.$chunk->xml.$chunk->poststack,
            $chunk->getWellFormedXML()
        );
    }

    /**
     * @test
     */
    public function getAChunksWellformedXMLWithPrestackXmlIdsRemoved()
    {
        $chunk            = new XMLChunk($this->setup);
        $chunk->prestack  = '<text xml:id="foo"><p xml:id="p" n="123">';
        $chunk->xml       = 'Hello world</p>';
        $chunk->poststack = '</text>';
        $this->assertEquals(
            '<text><p n="123">'.$chunk->xml.$chunk->poststack,
            $chunk->getWellFormedXML(true)
        );
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function tryingToGetPersistableDataForAnObjectWithIncompleteDataThrowsAnException()
    {
        $volume = new XMLChunk($this->setup);
        $volume->persistableData();
    }

    /**
     * @test
     */
    public function gettingThePersistableDataForAnObjectWithCompleteDataSucceeds()
    {
        $chunk          = new XMLChunk($this->setup);
        $chunk->page    = 15;
        $chunk->section = 37;
        $data           = $chunk->persistableData();
        $this->assertInternalType('array', $data);
        $this->assertSame(15, $chunk->page);
        $this->assertSame(37, $chunk->section);
    }
}
