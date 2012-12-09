<?php

namespace TEIShredder;

use InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\ElementGateway.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\ElementGateway
 */
class ElementGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Setup
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $setup     = prepare_default_data();
        $this->obj = new ElementGateway($setup->database, $setup->factory, $setup->prefix);
    }

    /**
     * @test
     */
    public function saveANewElement()
    {
        $element          = new Element();
        $element->xmlid   = "pb-15";
        $element->element = 'div';
        $element->page    = 23;
        $element->chunk   = 234;
        $this->obj->save($element);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function tryingToFetchAnElementByAnUnknownXmlidThrowsAnException()
    {
        $this->obj->findByIdentifier('element-123');
    }

    /**
     * @test
     */
    public function findAnElementByItsXmlid()
    {
        $this->obj->flush();

        // First, create object
        $element          = new Element();
        $element->xmlid   = "pb-15";
        $element->element = 'div';
        $element->page    = 23;
        $element->chunk   = 234;
        $this->obj->save($element);

        $obj = $this->obj->findByIdentifier('pb-15');
        $this->assertInstanceOf('TEIShredder\Element', $obj);
        $this->assertEquals('div', $element->element);
    }

    /**
     * @test
     */
    public function findAnElementByElementNameAndPage()
    {
        $this->obj->flush();

        // First, create object
        $element          = new Element();
        $element->xmlid   = "pb-15";
        $element->element = 'div';
        $element->page    = 23;
        $element->chunk   = 234;
        $this->obj->save($element);

        $objs = $this->obj->find('element = div', 'page = 23');
        $this->assertInternalType('array', $objs);
        $this->assertSame(1, count($objs));
        $this->assertInstanceOf('TEIShredder\Element', $objs[0]);
        $this->assertEquals("pb-15", $element->xmlid);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid property
     */
    public function tryingToFindAnElementByAnInvalidPropertyThrowsAnException()
    {
        $this->obj->find('invalid = 1');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function flushTheData()
    {
        // First, create object
        $element          = new Element();
        $element->xmlid   = "pb-15";
        $element->element = 'div';
        $element->page    = 23;
        $element->chunk   = 234;
        $this->obj->save($element);

        $this->obj->flush();

        // Now, we shouldn’t be able to find the element
        $this->obj->findByIdentifier('pb-15');
    }
}
