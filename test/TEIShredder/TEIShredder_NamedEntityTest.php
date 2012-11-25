<?php

namespace TEIShredder;

use \TEIShredder;
use \LogicException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_NamedEntity.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class NamedEntityTest extends \PHPUnit_Framework_TestCase
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
    public function createANewNamedEntity()
    {
        $entity = new NamedEntity($this->setup);
        $this->assertInstanceOf('\TEIShredder\NamedEntity', $entity);
    }

    /**
     * @test
     */
    public function getThePersistableDataForAnEntity()
    {
        $entity               = new NamedEntity($this->setup);
        $entity->xmlid        = 'element-01';
        $entity->page         = 123;
        $entity->domain       = 'person';
        $entity->identifier   = 'http://d-nb.info/gnd/118582143';
        $entity->contextstart = 'Painter ';
        $entity->notation     = 'Michelangelo';
        $entity->contextend   = ' lived in the renaissance';
        $entity->chunk        = 456;
        $entity->notationhash = 'd1f9cc6d';
        $data                 = $entity->persistableData();
        $this->assertInternalType('array', $data);
        $this->assertEquals(
            array(
                'xmlid'        => 'element-01',
                'page'         => 123,
                'domain'       => 'person',
                'identifier'   => 'http://d-nb.info/gnd/118582143',
                'contextstart' => 'Painter ',
                'notation'     => 'Michelangelo',
                'contextend'   => ' lived in the renaissance',
                'chunk'        => 456,
                'notationhash' => 'd1f9cc6d'
            ),
            $data
        );
    }

    /**
     * @test
     */
    public function theContextStartIsTruncatedWhenItExceedsACertainLength()
    {
        $entity               = new NamedEntity($this->setup);
        $in                   = str_repeat('abcdefgdefg ', 12);
        $entity->contextstart = $in;
        $this->assertTrue(strlen($entity->contextstart) < strlen($in));
        $this->assertSame('…', mb_substr($entity->contextstart, 0, 1));
    }

    /**
     * @test
     */
    public function theContextEndIsTruncatedWhenItExceedsACertainLength()
    {
        $entity             = new NamedEntity($this->setup);
        $in                 = str_repeat('abcdefgdefg ', 12);
        $entity->contextend = $in;
        $this->assertTrue(strlen($entity->contextend) < strlen($in));
        $this->assertSame('…', mb_substr($entity->contextend, -1));
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureANamedEntityRequiresAPageNumber()
    {
        $entity             = new NamedEntity($this->setup);
        $entity->domain     = 'person';
        $entity->identifier = 'http://d-nb.info/gnd/118582143';
        $entity->notation   = 'Michelangelo';
        $entity->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureANamedEntityRequiresADomain()
    {
        $entity             = new NamedEntity($this->setup);
        $entity->page       = 123;
        $entity->identifier = 'http://d-nb.info/gnd/118582143';
        $entity->notation   = 'Michelangelo';
        $entity->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureANamedEntityRequiresAKey()
    {
        $entity           = new NamedEntity($this->setup);
        $entity->page     = 123;
        $entity->domain   = 'person';
        $entity->notation = 'Michelangelo';
        $entity->persistableData();
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function makeSureANamedEntityRequiresANotation()
    {
        $entity             = new NamedEntity($this->setup);
        $entity->page       = 123;
        $entity->domain     = 'person';
        $entity->identifier = 'http://d-nb.info/gnd/118582143';
        $entity->persistableData();
    }
}
