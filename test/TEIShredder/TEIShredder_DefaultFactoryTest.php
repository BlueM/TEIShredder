<?php

namespace TEIShredder;

require_once __DIR__.'/../bootstrap.php';

/**
 * Test class for TEIShredder_Model.
 * @package TEIShredder
 * @subpackage Tests
 */
class DefaultFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var DefaultFactory $obj
	 */
	var $obj;

	/**
	 * Sets up the fixture
	 */
	function setUp() {
		$this->obj = new DefaultFactory;
	}

	/**
	 * Removes the fixture
	 */
	function tearDown() {
		unset($this->obj);
	}

	/**
	 * @test
	 */
	function createAPage() {
		$obj = $this->obj->createPage();
		$this->assertInstanceOf('\TEIShredder\Page', $obj);
	}

	/**
	 * @test
	 */
	function createPageGateway() {
		$obj = $this->obj->createPageGateway();
		$this->assertInstanceOf('\TEIShredder\PageGateway', $obj);
	}

	/**
	 * @test
	 */
	function createANamedEntity() {
		$obj = $this->obj->createNamedEntity();
		$this->assertInstanceOf('\TEIShredder\NamedEntity', $obj);
	}

	/**
	 * @test
	 */
	function createNamedEntityGateway() {
		$obj = $this->obj->createNamedEntityGateway();
		$this->assertInstanceOf('\TEIShredder\NamedEntityGateway', $obj);
	}
}

