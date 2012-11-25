<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Section.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class SectionTest extends \PHPUnit_Framework_TestCase
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
        $this->setup = prepare_default_data();
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
    public function createANewSection()
    {
        $section = new Section($this->setup);
        $this->assertInstanceOf('\TEIShredder\Section', $section);
        return $section;
    }

    /**
     * @test
     */
    public function getThePersistableDataOfAnObjectWithAllRequiredProperties()
    {
        $section          = new Section($this->setup);
        $section->id      = 13;
        $section->volume  = 2;
        $section->title   = 'Chapter 17';
        $section->page    = 57;
        $section->level   = 2;
        $section->element = 'div';
        $section->xmlid   = 'section-12345';
        $this->assertInternalType('array', $section->persistableData());
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureASectionRequiresAnId()
    {
        $section = new Section($this->setup);
        // $section->id = 13;
        $section->volume  = 2;
        $section->page    = 57;
        $section->level   = 2;
        $section->element = 'div';
        $section->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    function makeSureASectionRequiresAVolume()
    {
        $section          = new Section($this->setup);
        $section->id      = 13;
        $section->page    = 57;
        $section->level   = 2;
        $section->element = 'div';
        $section->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    function makeSureASectionRequiresAPage()
    {
        $section          = new Section($this->setup);
        $section->id      = 13;
        $section->volume  = 2;
        $section->level   = 2;
        $section->element = 'div';
        $section->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    function makeSureASectionRequiresAnElement()
    {
        $section         = new Section($this->setup);
        $section->id     = 13;
        $section->volume = 2;
        $section->page   = 57;
        $section->level  = 2;
        $section->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    function makeSureASectionRequiresALevel()
    {
        $section          = new Section($this->setup);
        $section->id      = 13;
        $section->volume  = 2;
        $section->page    = 57;
        $section->element = 'div';
        $section->persistableData();
    }
}
