<?php

namespace TEIShredder;

use PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\DefaultFactory.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\DefaultFactory
 */
class DefaultFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefaultFactory $obj
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $pdo       = new PDO('sqlite::memory:');
        $this->obj = new DefaultFactory($pdo);
    }

    /**
     * Removes the fixture
     */
    public function tearDown()
    {
        unset($this->obj);
    }

    /**
     * @test
     */
    public function createAPlaintextConverter()
    {
        $obj = $this->obj->createPlaintextConverter();
        $this->assertInstanceOf('TEIShredder\PlaintextConverter', $obj);
    }

    /**
     * @test
     */
    public function createATitleExtractor()
    {
        $obj = $this->obj->createTitleExtractor();
        $this->assertInstanceOf('TEIShredder\TitleExtractor', $obj);
    }

    /**
     * @test
     */
    public function createAPage()
    {
        $obj = $this->obj->createPage();
        $this->assertInstanceOf('TEIShredder\Page', $obj);
    }

    /**
     * @test
     */
    public function createPageGateway()
    {
        $obj = $this->obj->createPageGateway();
        $this->assertInstanceOf('TEIShredder\PageGateway', $obj);
    }

    /**
     * @test
     */
    public function createAVolume()
    {
        $obj = $this->obj->createVolume();
        $this->assertInstanceOf('TEIShredder\Volume', $obj);
    }

    /**
     * @test
     */
    public function createVolumeGateway()
    {
        $obj = $this->obj->createVolumeGateway();
        $this->assertInstanceOf('TEIShredder\VolumeGateway', $obj);
    }

    /**
     * @test
     */
    public function createSectionGateway()
    {
        $obj = $this->obj->createSectionGateway();
        $this->assertInstanceOf('TEIShredder\SectionGateway', $obj);
    }

    /**
     * @test
     */
    public function createASection()
    {
        $obj = $this->obj->createSection();
        $this->assertInstanceOf('TEIShredder\Section', $obj);
    }

    /**
     * @test
     */
    public function createElementGateway()
    {
        $obj = $this->obj->createElementGateway();
        $this->assertInstanceOf('TEIShredder\ElementGateway', $obj);
    }

    /**
     * @test
     */
    public function createAElement()
    {
        $obj = $this->obj->createElement();
        $this->assertInstanceOf('TEIShredder\Element', $obj);
    }

    /**
     * @test
     */
    public function createXMLChunkGateway()
    {
        $obj = $this->obj->createXMLChunkGateway();
        $this->assertInstanceOf('TEIShredder\XMLChunkGateway', $obj);
    }

    /**
     * @test
     */
    public function createAXMLChunk()
    {
        $obj = $this->obj->createXMLChunk();
        $this->assertInstanceOf('TEIShredder\XMLChunk', $obj);
    }

    /**
     * @test
     */
    public function createANamedEntity()
    {
        $obj = $this->obj->createNamedEntity();
        $this->assertInstanceOf('TEIShredder\NamedEntity', $obj);
    }

    /**
     * @test
     */
    public function createNamedEntityGateway()
    {
        $obj = $this->obj->createNamedEntityGateway();
        $this->assertInstanceOf('TEIShredder\NamedEntityGateway', $obj);
    }
}
