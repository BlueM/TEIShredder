<?php

namespace TEIShredder;

use PDO;
use UnexpectedValueException;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\Setup.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers TEIShredder\Setup
 */
class SetupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function creatingAnObjectWithDefaultCallbacksWorks()
    {
        $setup = new Setup(new PDO('sqlite::memory:'));
        $this->assertInstanceOf('TEIShredder\Setup', $setup);
    }

    /**
     * @test
     */
    public function creatingAnObjectWithACustomPlaintextCallbacksWorks()
    {
        $setup = new Setup(
            new PDO('sqlite::memory:'),
            null,
            '',
            function ($str) {
                return $str;
            } // Dummy plaintext conversion callback closure
        );
        $this->assertInstanceOf('TEIShredder\Setup', $setup);
    }

    /**
     * @test
     */
    public function creatingAnObjectWithACustomTitleExtractionCallbacksWorks()
    {
        $setup = new Setup(
            new PDO('sqlite::memory:'),
            null,
            '',
            null,
            function ($str) {
                return $str;
            } // Dummy title extraction callback closure
        );
        $this->assertInstanceOf('TEIShredder\Setup', $setup);
    }

    /**
     * @test
     */
    public function creatingAnObjectWithADifferentFactoryWorks()
    {
        /** @var $factory FactoryInterface */
        $factory = $this->getMock('TEIShredder\FactoryInterface');
        $setup   = new Setup(
            new PDO('sqlite::memory:'),
            $factory,
            '',
            null,
            function ($str) {
                return $str;
            } // Dummy title extraction callback closure
        );
        $this->assertInstanceOf('TEIShredder\Setup', $setup);
        $this->assertInstanceOf('TEIShredder\FactoryInterface', $setup->factory);
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid property name
     */
    public function tryingToGetAnInvalidClassMemberThrowsAnException()
    {
        $setup = new Setup(new PDO('sqlite::memory:'));
        $setup->foobar;
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function tryingToSetAnUnsettableClassMemberThrowsAnException()
    {
        $setup = new Setup(new PDO('sqlite::memory:'));
        $setup->database = array();
    }

    /**
     * @test
     */
    public function tryingToSetTheChunktagsToAnArrayWorks()
    {
        $setup = new Setup(new PDO('sqlite::memory:'));
        $array            = array('text', 'pb');
        $setup->chunktags = $array;
        $this->assertSame($array, $setup->chunktags);
    }
}
