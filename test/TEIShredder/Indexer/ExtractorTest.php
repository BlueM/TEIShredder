<?php

namespace TEIShredder\Indexer;

use TEIShredder\Setup;
use TEIShredder\XMLReader;
use TEIShredder\NamedEntity;
use InvalidArgumentException;

require_once __DIR__.'/../../bootstrap.php';

/**
 * Unit tests for TEIShredder\Indexer\Extractor.
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers \TEIShredder\Indexer\Extractor
 */
class ExtractorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Setup
     */
    protected $setup;

    /**
     * @var XMLReader
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

        $this->setup = new Setup($pdoMock);

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
    public function createAnExtractor()
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

        $extractor = new Extractor($this->setup, $this->xmlreader, $xml);
        $this->assertInstanceOf('TEIShredder\Indexer\Extractor', $extractor);
    }

    /**
     * @test
     */
    public function makeSureThatTheNumberOfElementAndEntitiesIsAsExpected()
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
            '<front><titlePart>Title</titlePart></front>'.
            '<body xml:id="body-1"><p xml:id="p-1">'.
            '<rs type="person" key="http://d-nb.info/gnd/118582143">Michelangelo</rs> and'.
            '<rs type="person" key="http://d-nb.info/gnd/118613723" xml:id="rs-1">'.
            'Shakespeare</rs> and'.
            '</p></body>'.
            '</text>'.
            '</TEI>';

        $extractor = new Extractor($this->setup, $this->xmlreader, $xml);

        // Create an entity gateway mock that expect save() to be called 2 times
        $entityGatewayMock = $this->getMockBuilder('TEIShredder\NamedEntityGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $entityGatewayMock->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf('TEIShredder\NamedEntity'));
        $reflm = new \ReflectionProperty($extractor, 'entityGateway');
        $reflm->setAccessible(true);
        $reflm->setValue($extractor, $entityGatewayMock);

        // Create an element gateway mock that expect save() to be called 3 times
        $entityGatewayMock = $this->getMockBuilder('TEIShredder\ElementGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $entityGatewayMock->expects($this->exactly(3))
            ->method('save')
            ->with($this->isInstanceOf('TEIShredder\Element'));
        $reflm = new \ReflectionProperty($extractor, 'elementGateway');
        $reflm->setAccessible(true);
        $reflm->setValue($extractor, $entityGatewayMock);

        // Do the processing
        $extractor->process();
    }

    /**
     * @test
     */
    public function makeSureThatAnEntityObjectIsAsExpected()
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
            '<front><titlePart>Title</titlePart></front>'.
            '<body xml:id="body-1">'.
            '<pb n="I" />'.
            '<pb n="II" />'.
            '<pb n="III" />'.
            '<p><lb />An artist named '.
            '<rs type="person" key="http://d-nb.info/gnd/118582143" xml:id="rs-1">'.
            'Michelangelo</rs>.<lb />Creator of <del>foo</del>masterpieces.'.
            '</p></body>'.
            '</text>'.
            '</TEI>';

        $extractor = new Extractor($this->setup, $this->xmlreader, $xml);

        // Create an entity gateway mock that expect save() to be called 2 times
        $entityGatewayMock = $this->getMockBuilder('TEIShredder\NamedEntityGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $entityGatewayMock->expects($this->exactly(1))
            ->method('save')
            ->will($this->returnCallback(
                    function($argument) {
                        if (! ($argument instanceof NamedEntity)) {
                            throw new InvalidArgumentException('Expected NamedEntity');
                        }
                        if (3 != $argument->page) {
                            throw new InvalidArgumentException('Expected page 3');
                        }
                        if ('An artist named ' != $argument->contextstart ||
                            '. Creator of masterpieces.' != $argument->contextend ||
                            'Michelangelo' != $argument->notation) {
                            throw new InvalidArgumentException('Context and/or notation mismatch');
                        }
                        if ('person' != $argument->domain ||
                            'http://d-nb.info/gnd/118582143' != $argument->identifier ||
                            'rs-1' != $argument->xmlid) {
                            throw new InvalidArgumentException('Attribute mismatch');
                        }
                    }
                ));
        $reflm = new \ReflectionProperty($extractor, 'entityGateway');
        $reflm->setAccessible(true);
        $reflm->setValue($extractor, $entityGatewayMock);

        // Do the processing
        $extractor->process();
    }
}
