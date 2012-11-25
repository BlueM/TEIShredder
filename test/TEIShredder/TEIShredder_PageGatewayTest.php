<?php

namespace TEIShredder;

use \TEIShredder;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_PageGateway.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class PageGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PageGateway
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $setup     = prepare_default_data();
        $this->obj = new PageGateway($setup->database, $setup->factory, $setup->prefix);
    }

    /**
     * @test
     */
    public function flushTheData()
    {
        $this->obj->flush();
    }

    /**
     * @test
     */
    public function saveANewPage()
    {
        $page            = new Page;
        $page->number    = 15;
        $page->xmlid     = "pb-15";
        $page->rend      = "normal";
        $page->n         = "XV";
        $page->volume    = 2;
        $page->plaintext = 'Foo';
        $this->obj->save($page);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function tryingToFetchAPageGatewayByAnUnknownPagenumberThrowsAnException()
    {
        $this->obj->findByIdentifier(9999999);
    }

    /**
     * @test
     */
    public function findAPageByItsNumber()
    {
        // First, create object
        $page         = new Page;
        $page->number = 20;
        $page->volume = 5;
        $this->obj->save($page);

        $obj = $this->obj->findByIdentifier(20);
        $this->assertInstanceOf('\TEIShredder\Page', $obj);
        $this->assertEquals(5, $page->volume);
    }

    /**
     * @test
     */
    public function findAllPages()
    {
        $page         = new Page;
        $page->number = 20;
        $page->volume = 5;
        $this->obj->save($page);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertTrue(0 < count($objs));
        foreach ($objs as $obj) {
            $this->assertInstanceOf('\TEIShredder\Page', $obj);
        }
    }

    /**
     * @test
     */
    public function findPagesMatchingGivenCriteria()
    {
        for ($i = 1; $i <= 3; $i++) {
            $page         = new Page;
            $page->number = $i;
            $page->volume = $i;
            $this->obj->save($page);
        }

        $objs = $this->obj->find('volume = 2');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(1 == count($objs));
        $this->assertInstanceOf('\TEIShredder\Page', $objs[0]);
    }

    /**
     * @test
     */
    public function findMultiplePages()
    {
        for ($i = 20; $i < 25; $i++) {
            $page         = new Page;
            $page->number = $i;
            $page->volume = 1;
            $page->n      = "Page $i";
            $this->obj->save($page);
        }

        $objs = $this->obj->findMultiple(array(22, 23));
        $this->assertInternalType('array', $objs);
        $this->assertTrue(2 == count($objs));
        $this->assertInstanceOf('\TEIShredder\Page', $objs[0]);
        $this->assertInstanceOf('\TEIShredder\Page', $objs[0]);
        $this->assertEquals('Page 22', $objs[0]->n);
        $this->assertEquals('Page 23', $objs[1]->n);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid property
     */
    public function tryingToFindAPageByAnInvalidPropertyThrowsAnException()
    {
        $this->obj->find('invalid = 1');
    }
}
