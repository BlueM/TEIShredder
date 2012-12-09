<?php

namespace TEIShredder;

use LogicException;
use PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\Page.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
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
        $this->setup = new Setup(new PDO('sqlite::memory:'));
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
     */
    public function createANewPage()
    {
        $page = new Page($this->setup);
        $this->assertInstanceOf('TEIShredder\Page', $page);
        return $page;
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAPageRequiresANumber()
    {
        $page         = new Page($this->setup);
        $page->volume = 2;
        $page->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAPageRequiresAVolume()
    {
        $page         = new Page($this->setup);
        $page->number = 1234;
        $page->persistableData();
    }

    /**
     * @test
     */
    public function getThePersistableDataOfAnObjectWithAllRequiredProperties()
    {
        $page         = new Page($this->setup);
        $page->volume = 2;
        $page->number = 1234;
        $this->assertInternalType('array', $page->persistableData());
    }
}
