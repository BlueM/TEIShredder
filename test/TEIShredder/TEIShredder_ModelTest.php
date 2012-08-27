<?php

namespace TEIShredder;

use \TEIShredder;
use \UnexpectedValueException;
use \PDO;

require_once __DIR__.'/../bootstrap.php';

class DummyModelSubclass extends Model {
	protected $foo = 'bar';
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
		$obj = new Model($this->setup);
		$this->assertInstanceOf('\TEIShredder\Model', $obj);
		return $obj;
	}

	/**
	 * @test
	 * @depends createAnObject
	 * @expectedException UnexpectedValueException
	 */
	function tryingToSetAnInvalidPropertyThrowsAnException(Model $obj) {
		$obj->foo= 'bar';
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
		$obj = new DummyModelSubclass($this->setup);
		$this->assertSame('TEIShredder\DummyModelSubclass [foo: bar]', (string)$obj);
	}

}

