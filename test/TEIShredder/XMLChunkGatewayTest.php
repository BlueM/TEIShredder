<?php

namespace TEIShredder;

use InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\XMLChunkGateway
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\XMLChunkGateway
 */
class XMLChunkGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var XMLChunkGateway
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $setup     = prepare_default_data();
        $this->obj = new XMLChunkGateway($setup->database, $setup->factory, $setup->prefix);
    }

    /**
     * @test
     */
    public function saveAnXmlChunk()
    {
        $this->obj->flush();

        $chunk          = new XMLChunk();
        $chunk->page    = 3;
        $chunk->section = 4;
        $chunk->xml     = '<foot/>';
        $this->obj->save($chunk);

        $chunks = $this->obj->findByPageNumber(3);
        $this->assertInternalType('array', $chunks);
        $this->assertInstanceOf('TEIShredder\XMLChunk', $chunks[0]);
    }

    /**
     * @test
     */
    public function findAnXmlChunkByItsId()
    {
        $this->obj->flush();

        $chunk          = new XMLChunk();
        $chunk->id      = 123;
        $chunk->page    = 3;
        $chunk->section = 4;
        $chunk->xml     = '<foot/>';
        $this->obj->save($chunk);

        $chunk = $this->obj->findByIdentifier(123);
        $this->assertInstanceOf('TEIShredder\XMLChunk', $chunk);
        $this->assertEquals(123, $chunk->id);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid chunk number
     */
    public function tryingToFindAnXmlChunkByAnInvalidIdThrowsAnException()
    {
        $this->obj->findByIdentifier(99999999);
    }

    /**
     * @test
     */
    public function flushTheData()
    {
        $chunk          = new XMLChunk();
        $chunk->page    = 3;
        $chunk->section = 4;
        $chunk->xml     = '<foot/>';
        $this->obj->save($chunk);

        $chunks = $this->obj->find();
        $this->assertInternalType('array', $chunks);
        $this->assertSame(1, count($chunks));

        $this->obj->flush();

        $chunks = $this->obj->find();
        $this->assertInternalType('array', $chunks);
        $this->assertSame(0, count($chunks));
    }
}
