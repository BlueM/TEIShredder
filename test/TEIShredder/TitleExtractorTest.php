<?php

namespace TEIShredder;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TitleExtractor
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\TitleExtractor
 */
class TitleExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TitleExtractor
     */
    protected $object;

    /**
     * @test
     */
    public function extractTheTitleFromTei()
    {
        $plaintextConverterMock = $this->getMockBuilder('TEIShredder\PlaintextConverter')
            ->getMock();
        $plaintextConverterMock->expects($this->once())
            ->method('convert')
            ->with('<head>A headline that makes <hi rend="italic">little</hi> sense.</head>')
            ->will($this->returnValue('A headline that makes little sense.'));

        $factoryMock = $this->getMockBuilder('TEIShredder\FactoryInterface')
            ->getMock();
        $factoryMock->expects($this->once())
            ->method('createPlaintextConverter')
            ->will($this->returnValue($plaintextConverterMock));

        $xml = '<div>'.
               '<head>A headline that makes <hi rend="italic">little</hi> sense.</head>'.
               '<p>Lorem ipsum etc.</p>'.
               '</div>';

        $te     = new TitleExtractor($factoryMock->createPlaintextConverter());
        $actual = $te->extractTitle($xml);
        $this->assertSame('A headline that makes little sense.', $actual);
    }

    /**
     * @test
     */
    public function tryingToExtractTheTitleFromTeiReturnsAnEmptyStringIfThereIsNoHeadElement()
    {
        $plaintextConverterMock = $this->getMock('TEIShredder\PlaintextConverter');
        $plaintextConverterMock->expects($this->never())
            ->method('convert');

        $te     = new TitleExtractor($plaintextConverterMock);
        $actual = $te->extractTitle('<div><p>Lorem ipsum etc.</p></div>');
        $this->assertSame('', $actual);
    }
}
