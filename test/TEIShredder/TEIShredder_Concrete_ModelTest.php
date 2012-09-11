<?php

namespace TEIShredder;

use \TEIShredder;
use \UnexpectedValueException;
use \PDO;

require_once __DIR__.'/../bootstrap.php';

/**
 * Concrete model subclass for testing purposes
 */
class ConcreteModel extends Model {
	protected $a  = 'A';
	protected $b  = 'B';
	protected $_c = 'C';

	/**
	 * Returns an associative array of property=>value pairs to be
	 * processed by a persistence layer.	 */
	public function persistableData() {
		return $this->toArray();
	}
}

/**
 * Test class for TEIShredder_Model.
 * @package TEIShredder
 * @subpackage Tests
 */
class ModelTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Setup
	 */
	var $setup;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->setup = new Setup(new PDO('sqlite::memory:'));
	}

	/**
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->setup);
	}

	/**
	 * @test
	 */
	function createAnObject() {
		$obj = new ConcreteModel($this->setup);
		$this->assertInstanceOf('\TEIShredder\ModelSubclassStub', $obj);
		return $obj;
	}

	/**
	 * @test
	 * @depends createAnObject
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Model $obj) {
		$obj->nonexistent = 'bar';
	}

	/**
	 * @test
	 * @depends createAnObject
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnUnsettablePropertyThrowsAnException(Model $obj) {
		$obj->_setup = 'something';
	}

	/**
	 * @test
	 */
	function testTypeCastingToString() {
		$obj = new ConcreteModel($this->setup);
		$this->assertSame('TEIShredder\ModelSubclassStub [foo: bar]', (string)$obj);
	}

	/**
	 * @test
	 */
	function testTypeCastingToString() {
		$obj = new ConcreteModel($this->setup);
		$this->assertSame('TEIShredder\ModelSubclassStub [foo: bar]', (string)$obj);
	}

}

