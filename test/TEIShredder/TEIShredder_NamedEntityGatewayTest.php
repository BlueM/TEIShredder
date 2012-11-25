<?php

namespace TEIShredder;

use \TEIShredder;
use PDO;
use \InvalidArgumentException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for NamedEntityGateway
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class NamedEntityGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var NamedEntityGateway
     */
    protected $obj;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $setup     = prepare_default_data();
        $this->obj = new NamedEntityGateway($setup->database, $setup->factory, $setup->prefix);
    }

    /**
     * @test
     */
    public function flushTheData()
    {
        $this->obj->flush();
        $objs = $this->obj->find();
        $this->assertTrue(0 == count($objs));
    }

    /**
     * @test
     */
    public function saveANamedEntity()
    {

        $this->obj->flush();

        $ent             = new NamedEntity();
        $ent->xmlid      = 'entity-123';
        $ent->page       = 22;
        $ent->chunk      = 33;
        $ent->domain     = 'demo';
        $ent->identifier = '4711';
        $ent->notation   = 'Named Entity';
        $this->obj->save($ent);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[0]);
    }

    /**
     * @test
     */
    public function findAllNamedEntities()
    {

        $this->obj->flush();

        $ent             = new NamedEntity();
        $ent->xmlid      = 'entity-1';
        $ent->page       = 22;
        $ent->chunk      = 33;
        $ent->domain     = 'person';
        $ent->identifier = 44;
        $ent->notation   = 'Named Entity 1';
        $this->obj->save($ent);

        $ent             = new NamedEntity;
        $ent->xmlid      = 'entity-123';
        $ent->page       = 55;
        $ent->chunk      = 66;
        $ent->domain     = 'person';
        $ent->identifier = 77;
        $ent->notation   = 'Named Entity 2';
        $this->obj->save($ent);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertTrue(2 == count($objs));
        $this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[0]);
        $this->assertSame('Named Entity 1', $objs[0]->notation);
        $this->assertInstanceOf('\TEIShredder\NamedEntity', $objs[1]);
        $this->assertSame('Named Entity 2', $objs[1]->notation);
    }

    /**
     * @test
     */
    public function findEntitiesBySearchCriteria()
    {

        $ent             = new NamedEntity();
        $ent->xmlid      = 'e1';
        $ent->page       = 22;
        $ent->chunk      = 33;
        $ent->domain     = 'person';
        $ent->identifier = 44;
        $ent->notation   = 'Entity 1';
        $this->obj->save($ent);

        $ent             = new NamedEntity;
        $ent->xmlid      = 'e2';
        $ent->page       = 55;
        $ent->chunk      = 66;
        $ent->domain     = 'place';
        $ent->identifier = 44;
        $ent->notation   = 'Entity 2';
        $this->obj->save($ent);

        $objs = $this->obj->find();
        $this->assertInternalType('array', $objs);
        $this->assertTrue(2 == count($objs));

        $objs = $this->obj->find('domain = person');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(1 == count($objs));

        $objs = $this->obj->find('domain == artwork');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(0 == count($objs));

        $objs = $this->obj->find('identifier != 19');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(2 == count($objs));

        $objs = $this->obj->find('identifier != 19');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(2 == count($objs));

        $objs = $this->obj->find('page >= 22', ' chunk<>66');
        $this->assertInternalType('array', $objs);
        $this->assertTrue(1 == count($objs));
        $this->assertSame('Entity 1', $objs[0]->notation);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid property
     */
    public function tryingToFindANamedEntityByAnInvalidPropertyThrowsAnException()
    {
        $this->obj->find('invalid = 1');
    }

    /**
     * @test
     * @covers TEIShredder\NamedEntityGateway::findDistinctNotations
     */
    public function getAllDistinctNotations()
    {
        $pdoStatementMock = $this->getMockBuilder('PDOStatement')
            ->setConstructorArgs(array())
            ->getMock();
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with(array('person', 12345));
        $pdoStatementMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->will(
            $this->returnValue(
                array(
                    array('notation' => 'Name2', 'notationhash' => 'fb554b3bb6'),
                    array('notation' => 'Name1', 'notationhash' => 'b38422f17e'),
                    array('notation' => 'Name1', 'notationhash' => 'b38422f17e'),
                    array('notation' => 'Name2', 'notationhash' => 'fb554b3bb6'),
                    array('notation' => 'Name1', 'notationhash' => 'b38422f17e'),
                )
            )
        );

        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($pdoStatementMock));

        $reflm = new \ReflectionProperty($this->obj, 'db');
        $reflm->setAccessible(true);
        $reflm->setValue($this->obj, $pdoMock);

        $actual = $this->obj->findDistinctNotations('person', 12345);
        $this->assertInternalType('array', $actual);
        $this->assertSame(2, count($actual));
        $this->assertSame(array('Name1', 3, 'b38422f17e'), $actual[0]);
        $this->assertSame(array('Name2', 2, 'fb554b3bb6'), $actual[1]);
    }
}
