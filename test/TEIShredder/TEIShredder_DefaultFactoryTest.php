<?php

namespace TEIShredder;

use \PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Model.
 *
 * @package    TEIShredder
 * @subpackage Tests
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
    public function createAPage()
    {
        $obj = $this->obj->createPage();
        $this->assertInstanceOf('\TEIShredder\Page', $obj);
    }

    /**
     * @test
     */
    public function createPageGateway()
    {
        $obj = $this->obj->createPageGateway();
        $this->assertInstanceOf('\TEIShredder\PageGateway', $obj);
    }

    /**
     * @test
     */
    public function createAVolume()
    {
        $obj = $this->obj->createVolume();
        $this->assertInstanceOf('\TEIShredder\Volume', $obj);
    }

    /**
     * @test
     */
    public function createVolumeGateway()
    {
        $obj = $this->obj->createVolumeGateway();
        $this->assertInstanceOf('\TEIShredder\VolumeGateway', $obj);
    }

    /**
     * @test
     */
    public function createANamedEntity()
    {
        $obj = $this->obj->createNamedEntity();
        $this->assertInstanceOf('\TEIShredder\NamedEntity', $obj);
    }

    /**
     * @test
     */
    public function createNamedEntityGateway()
    {
        $obj = $this->obj->createNamedEntityGateway();
        $this->assertInstanceOf('\TEIShredder\NamedEntityGateway', $obj);
    }
}
