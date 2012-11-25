<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Element.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class ElementTest extends \PHPUnit_Framework_TestCase
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
    public function createANewElement()
    {
        $element = new Element($this->setup);
        $this->assertInstanceOf('\TEIShredder\Element', $element);
        return $element;
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAnElementRequiresAnXmlid()
    {
        $element          = new Element($this->setup);
        $element->element = 'rs';
        $element->page    = 57;
        $element->chunk   = 99;
        $element->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAnElementRequiresAPage()
    {
        $element          = new Element($this->setup);
        $element->xmlid   = 'element-01';
        $element->element = 'rs';
        $element->chunk   = 99;
        $element->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAnElementRequiresAChunk()
    {
        $element          = new Element($this->setup);
        $element->xmlid   = 'element-01';
        $element->element = 'rs';
        $element->page    = 57;
        $element->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureAnElementRequiresAnElement()
    {
        $element        = new Element($this->setup);
        $element->xmlid = 'element-01';
        $element->chunk = 123;
        $element->page  = 57;
        $element->persistableData();
    }

    /**
     * @test
     */
    public function getThePersistableDataOfAnObjectWithAllRequiredProperties()
    {
        $element          = new Element($this->setup);
        $element->xmlid   = 'element-01';
        $element->element = 'rs';
        $element->page    = 57;
        $element->chunk   = 99;
        $element->persistableData();
        $this->assertInternalType('array', $element->persistableData());
    }
}
