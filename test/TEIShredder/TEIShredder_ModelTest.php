<?php

namespace TEIShredder;

use \UnexpectedValueException;
use \PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Concrete Model class for testing purposes
 */
class ConcreteModel extends Model
{
    /**
     * @var string
     */
    protected $a = 'A';
    protected $b = 'B';

    /**
     * Returns data to be passed to a persistence layer.
     */
    public function persistableData()
    {
        return $this->toArray();
    }
}

/**
 * Test class for TEIShredder_Model.
 *
 * @package    TEIShredder
 * @subpackage Tests
 */
class ModelTest extends \PHPUnit_Framework_TestCase
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
    public function createAnObject()
    {
        $obj = new ConcreteModel($this->setup);
        $this->assertInstanceOf('\TEIShredder\ConcreteModel', $obj);
        return $obj;
    }

    /**
     * @test
     * @depends createAnObject
     */
    public function settingASettablePropertyWorks(Model $obj)
    {
        $obj->a = 'A value';
        $this->assertSame('A value', $obj->a);
    }

    /**
     * @test
     * @depends createAnObject
     * @expectedException UnexpectedValueException
     */
    public function tryingToGetAnInvalidPropertyThrowsAnException(Model $obj)
    {
        $obj->nonexistent;
    }

    /**
     * @test
     * @depends createAnObject
     * @expectedException UnexpectedValueException
     */
    public function tryingToSetAnInvalidPropertyThrowsAnException(Model $obj)
    {
        $obj->nonexistent = 'bar';
    }

    /**
     * @test
     * @depends createAnObject
     * @expectedException UnexpectedValueException
     */
    public function tryingToSetAnUnsettablePropertyThrowsAnException(Model $obj)
    {
        $obj->_setup = 'something';
    }

    /**
     * @test
     */
    public function gettingThePersistableDataWorks()
    {
        $obj = new ConcreteModel($this->setup);
        $this->assertInternalType('array', $obj->persistableData());
    }

    /**
     * @test
     */
    public function testTypeCastingToString()
    {
        $obj = new ConcreteModel($this->setup);
        $this->assertSame('TEIShredder\ConcreteModel [a: A, b: B]', (string)$obj);
    }
}
