<?php

/**
 * Copyright (c) 2012, Carsten Blüm <carsten@bluem.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace TEIShredder\Indexer;

use TEIShredder\Setup;
use TEIShredder\XMLReader;

/**
 * Class for extracting some tags from a TEI Lite document and for
 * transferring them to a RDBMS.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Extractor extends Base
{

    /**
     * Array of elements that are regarded as distinct text containers
     *
     * @var string[] Indexed array of element names
     */
    public $containertags = array(
        'byline', 'docImprint', 'head', 'item', 'l', 'note', 'p', 'titlePart'
    );

    /**
     * @var \SplObjectStorage
     */
    protected $entities;

    /**
     * Stack of currently "open" entity tags
     *
     * @var array Indexed array of xml:id values
     */
    protected $entityStack = array();

    /**
     * Stack of counter numbers of currently "open" container tags
     *
     * @var array Indexed array of index/counter numbers
     */
    protected $containerStack = array();

    /**
     * Indexed array of elements that are currently open. Will contain
     * something like array('text', 'body', 'div', 'p')
     *
     * @var string
     */
    protected $elementStack = array();

    /**
     * Array that contains the text chunks in which the notations occur
     *
     * @var array Indexed array
     */
    protected $containers = array();

    /**
     * @var \TEIShredder\PlaintextConverter
     */
    protected $plaintextConverter;

    /**
     * @var \TEIShredder\NamedEntityGateway
     */
    protected $entityGateway;

    /**
     * @var \TEIShredder\ElementGateway
     */
    protected $elementGateway;

    /**
     * Constructor.
     *
     * @param Setup     $setup
     * @param XMLReader $xmlreader
     * @param string    $xml Input XML
     * @param XMLReader $xmlreader
     */
    public function __construct(Setup $setup, XMLReader $xmlreader, $xml)
    {
        parent::__construct($setup, $xmlreader, $xml);
        $this->plaintextConverter = $setup->factory->createPlaintextConverter();
        $this->entityGateway  = $setup->factory->createNamedEntityGateway();
        $this->elementGateway = $setup->factory->createElementGateway();
        $this->entities       = new \SplObjectStorage;
    }

    /**
     * Method that's called when the stream reaches an opening or empty tag.
     *
     * @return mixed
     */
    protected function nodeOpen()
    {
        // Keep track of the chunk number
        if (in_array($this->r->localName, $this->setup->chunktags) or
            in_array($this->r->localName, $this->setup->nostacktags)
        ) {
            $this->currentChunk++;
        }

        // New page: increase page number, save element and return
        if ('pb' == $this->r->localName) {
            $this->page++;
            $this->newElement();
            return;
        }

        $containerindex = (int)end($this->containerStack);

        // In case of <lb/> or <milestone/>, add space to the enclosing
        // container (if any), but don't do anything else, i.e.: return.
        if ('lb' == $this->r->localName ||
            'milestone' == $this->r->localName
        ) {
            if (empty($this->containers[$containerindex])) {
                $this->containers[$containerindex] = '';
            }
            $this->containers[$containerindex] .= ' ';
            return;
        }

        // Update the elements stack, unless it's an empty element
        if (!$this->r->isEmptyElement) {
            $this->elementStack[] = $this->r->localName;
        }

        // Create a new element
        $this->newElement();

        if ($this->r->localName == 'rs') {
            // Named entity
            $entity             = $this->setup->factory->createNamedEntity($this->setup);
            $entity->xmlid      = $this->r->getAttribute('xml:id');
            $entity->page       = $this->page;
            $entity->chunk      = $this->currentChunk;
            $entity->domain     = $this->r->getAttribute('type');
            $entity->identifier = $this->r->getAttribute('key'); // Reminder: could be multiple
            $this->entities->attach($entity, $containerindex);

            $this->entityStack[] = $entity->xmlid;
            $this->containers[$containerindex] .= '<:'.$entity->xmlid.'>';
        } elseif (in_array($this->r->localName, $this->containertags)) {
            // Container tags
            $this->registerNewContainer();
        }
    }

    /**
     * Completes all the named entities' data accumulated before (plus some
     * additional data, such as some context string) to the database.
     */
    protected function save()
    {
        $this->entities->rewind();

        while ($this->entities->valid()) {

            $entity         = $this->entities->current();
            $containerindex = $this->entities->getInfo();

            // Get the text context.
            list($entity->contextstart, $entity->notation, $entity->contextend)
                = $this->extractText($entity->xmlid, $containerindex);

            foreach (explode(' ', $entity->identifier) as $identifier) {
                // Each entry in array $tag['key'] points to a different target. If
                // there are multiple entries, we have to add multiple records to
                // support links with multiple targets. Note: save() below will
                // always insert a new record, therefore it doesn't matter that
                // we pass the same object several times.
                $entity->identifier = $identifier;
                $this->entityGateway->save($entity);
            }

            $this->entities->detach($entity);
            unset($entity);
        }
    }

    /**
     * Extracts the entity's notation and surrounding context from the text
     *
     * @param string $xmlid          Value of @xml:id attribute of element whose
     *                               context is to be returned.
     * @param int    $containerindex Internal container counter/index
     *
     * @return array
     */
    protected function extractText($xmlid, $containerindex)
    {
        // Insert marker for "own" notation, then remove other notations
        $context = preg_replace(
            '#</?:[^>]+>#',
            '',
            str_replace(
                array('<:'.$xmlid.'>', '</:'.$xmlid.'>'),
                '###',
                $this->containers[$containerindex]
            )
        );

        // Convert the context to plaintext. Therefore, escape special chars
        // in the plaintext, as the plaintext conversion code should expect XML.
        $context = trim(
            preg_replace(
                '#\s+#u',
                ' ',
                $this->plaintextConverter->convert(htmlspecialchars($context))
            )
        );

        return explode('###', $context);
    }

    /**
     * Records occurrences of any elements that have an xml:id attribute.
     */
    protected function newElement()
    {
        if (null == $xmlid = $this->r->getAttribute('xml:id')) {
            // No xml:id attribute >> Ignore
            return;
        }

        $e          = $this->setup->factory->createElement($this->setup);
        $e->xmlid   = $xmlid;
        $e->element = $this->r->localName;
        $e->page    = $this->page;
        $e->chunk   = $this->currentChunk;
        $this->elementGateway->save($e);
    }

    /**
     * {@inheritDoc}
     */
    protected function preProcessAction()
    {
        $this->elementGateway->flush($this->setup);
        $this->entityGateway->flush($this->setup);
    }

    /**
     * Called when a new container tag is encountered (i.e.: an XML
     * element which is in $this->containertags)
     */
    protected function registerNewContainer()
    {
        static $index = 0;
        $this->containerStack[]   = ++$index;
        $this->containers[$index] = '';
    }

    /**
     * Method that's called when the input stream reaches a text
     * node or significant whitespace
     */
    protected function nodeContent()
    {
        if (empty($this->containerStack)) {
            // Not inside a container -- ignore
            return;
        }

        if (in_array(end($this->elementStack), $this->setup->ignorabletags)) {
            return;
        }

        $containerindex = end($this->containerStack);
        $this->containers[$containerindex] .= $this->r->value;
    }

    /**
     * Method that's called when the input stream reaches a closing tag
     */
    protected function nodeClose()
    {
        // Remove the closed element from the element stack
        array_pop($this->elementStack);

        if (in_array($this->r->localName, $this->containertags)) {
            array_pop($this->containerStack);
        }

        if ($this->r->localName == 'rs') {
            // Closing entity tag
            $entityindex    = array_pop($this->entityStack);
            $containerindex = end($this->containerStack);
            $this->containers[$containerindex] .= '</:'.$entityindex.'>';
        }
    }
}
