<?php

namespace TEIShredder\Indexer;

use \TEIShredder;
use \RuntimeException;
use TEIShredder\XMLReader;

require_once __DIR__.'/../../bootstrap.php';

/**
 * Unit tests for TEIShredder\Indexer\Chunker.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers \TEIShredder\Indexer\Base
 * @covers \TEIShredder\Indexer\Chunker
 */
class ChunkerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \TEIShredder\Setup $setup
     */
    protected $setup;

    /**
     * @var XMLReader $xmlreader
     */
    protected $xmlreader;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $pdoStmMock = $this->getMockBuilder('PDOStatement')
            ->getMock();

        $pdoMock = $this->getMockBuilder('PDO')
            ->setConstructorArgs(array('sqlite::memory:'))
            ->getMock();
        $pdoMock->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($pdoStmMock));
        $pdoMock->expects($this->any())
            ->method('query')
            ->will($this->returnValue($pdoStmMock));

        $this->setup = new \TEIShredder\Setup($pdoMock);

        $this->xmlreader = new XMLReader;
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
    public function createAChunker()
    {
        $xml = '<TEI xmlns="http://www.tei-c.org/ns/1.0">'.
               '<teiHeader>'.
               '<fileDesc>'.
               '<titleStmt><title>...</title></titleStmt>'.
               '<publicationStmt><p>...</p></publicationStmt>'.
               '<sourceDesc><p>...</p></sourceDesc>'.
               '</fileDesc>'.
               '</teiHeader>'.
               '<text>'.
               '<front><titlePart>Title 2</titlePart></front>'.
               '<body><p>...</p></body>'.
               '</text>'.
               '</TEI>';

        $chunker = new Chunker($this->setup, $this->xmlreader, $xml);
        $this->assertInstanceOf('\\'.__NAMESPACE__.'\\Chunker', $chunker);
    }

    /**
     * @test
     */
    public function runTheChunker()
    {
        $xml = '<TEI xmlns="http://www.tei-c.org/ns/1.0">'.
            '<teiHeader>'.
            '<fileDesc>'.
            '<titleStmt><title>...</title></titleStmt>'.
            '<publicationStmt><p>...</p></publicationStmt>'.
            '<sourceDesc><p>...</p></sourceDesc>'.
            '</fileDesc>'.
            '</teiHeader>'.
            '<text>'.
            '<front>'.
            '<titlePage><docTitle><titlePart>Title 2</titlePart></docTitle></titlePage>'.
            '</front>'.
            '<body>'.
            '<pb n="1" />'.
            '<milestone unit="column" n="left" />'.
            '<div><head>Divhead</head><p>...</p></div></body>'.
            '</text>'.
            '</TEI>';
        $chunker = new Chunker($this->setup, $this->xmlreader, $xml);
        $chunker->process();
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Multiple <titlePart>
     */
    public function makeSureAChunkerThrowsAnExceptionIfThereAreSeveralTitlesForAVolume()
    {
        $xml = <<<_XML_
<TEI xmlns="http://www.tei-c.org/ns/1.0">
  <teiHeader>
    <fileDesc>
      <titleStmt><title>...</title></titleStmt>
      <publicationStmt><p>...</p></publicationStmt>
      <sourceDesc><p>...</p></sourceDesc>
    </fileDesc>
  </teiHeader>
  <text>
    <front>
  		<titlePart>Title 1</titlePart>
  		<titlePart>Title 2</titlePart>
  	</front>
    <body>
      <p>...</p>
    </body>
  </text>
</TEI>
_XML_;

        $chunker = new Chunker(
            $this->setup,
            $this->xmlreader,
            $xml
        );

        $chunker->process();
    }

    /**
     * @test
     */
    public function runAChunkerWithTextbeforepbSetToOff()
    {
        $xml = <<<_XML_
<TEI xmlns="http://www.tei-c.org/ns/1.0">
  <teiHeader>
    <fileDesc>
      <titleStmt><title>...</title></titleStmt>
      <publicationStmt><p>...</p></publicationStmt>
      <sourceDesc><p>...</p></sourceDesc>
    </fileDesc>
  </teiHeader>
  <group>

  <pb n="1" />
  <text>
    <titlePart>Vol1</titlePart>
    <body>
      <p>...</p>
    </body>
  </text>

  <pb n="2" />
  <text>
    <titlePart>Vol2</titlePart>
    <body>
      <p>...</p>
    </body>
  </text>
  </group>
</TEI>
_XML_;

        $chunker = new Chunker(
            $this->setup,
            $this->xmlreader,
            $xml
        );
        $chunker->textBeforePb = false;

        // Create a volume gateway mock
        $volumeGatewayMock = $this->getMockBuilder('\TEIShredder\VolumeGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $volumeGatewayMock->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf('\TEIShredder\Volume'));
        $reflm = new \ReflectionProperty($chunker, 'volumeGateway');
        $reflm->setAccessible(true);
        $reflm->setValue($chunker, $volumeGatewayMock);

        $chunker->process();
    }
}

