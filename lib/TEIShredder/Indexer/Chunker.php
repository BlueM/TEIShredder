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

use TEIShredder\XMLReader;
use TEIShredder\Setup;

/**
 * Class for splitting a TEI document in well-formed XML chunks.
 *
 * Known shortcomings: Cannot handle nested <group> tags.
 * Unknown shortcomings: Surely lots!
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Chunker extends Base
{

    /**
     * Does a text's <text> element enclose its first <pb /> element?
     * Set to false, if the order is <pb /> ... <text>
     *
     * @var bool
     */
    public $textBeforePb = true;

    /**
     * String that will be used as title for <titlePage> sections
     *
     * @var string
     */
    public $titlePageLabel = 'Title page';

    /**
     * Array of tags that are yet unclosed at the point
     * in the text where this chunk starics.
     *
     * @var array Indexed array
     */
    protected $prestack = array();

    /**
     * Array of tags that are yet unclosed at the point in the text
     * where this chunk ends
     *
     * @var array Indexed array
     */
    protected $poststack = array();

    /**
     * Flag: are we somewhere inside of a <text>?
     *
     * @var bool
     */
    protected $insidetext = false;

    /**
     * Array for keeping track of various internal variables.
     *
     * @var array
     */
    protected $data = array(
        'currTextStart' => 0, // Page number at which the current <text> started
        'currentVolume' => 0, // Current volume number
        'volTitles'     => array(), // Titles of the volumes
    );

    /**
     * ID of section currently being processed
     *
     * @var int
     */
    protected $currentSection = 0;

    /**
     * Contains XMLChunk instances
     *
     * @var array Associative array containing id=>XMLChunk instance pairs
     */
    protected $chunks = array();

    /**
     * Variable used to collect the current chunk's contents
     *
     * @var string
     */
    protected $xml = null;

    /**
     * Current <milestone />
     * @unit and @n, concatenated
     * @var string
     */
    protected $milestone = null;

    /**
     * Text structure level. Note: Only named sections (those with
     * a <head> element) are counted.
     *
     * @var int
     */
    protected $level = 0;

    /**
     * @var \TEIShredder\PlaintextConverter
     */
    protected $plaintextConverter;

    /**
     * @var \TEIShredder\TitleExtractor
     */
    protected $titleExtractor;

    /**
     * The current page object
     *
     * @var \TEIShredder\Page
     */
    protected $pageObj;

    /**
     * @var \TEIShredder\VolumeGateway
     */
    protected $volumeGateway;

    /**
     * @var \TEIShredder\SectionGateway
     */
    protected $sectionGateway;

    /**
     * @var \TEIShredder\PageGateway
     */
    protected $pageGateway;

    /**
     * @var \TEIShredder\XMLChunkGateway
     */
    protected $xmlChunkGateway;

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
        $this->titleExtractor     = $setup->factory->createTitleExtractor();
        $this->volumeGateway      = $setup->factory->createVolumeGateway();
        $this->sectionGateway     = $setup->factory->createSectionGateway();
        $this->pageGateway        = $setup->factory->createPageGateway();
        $this->xmlChunkGateway    = $setup->factory->createXMLChunkGateway();
    }

    /**
     * Method that's called when the input stream reaches an opening
     * tag (or an empty tag)
     */
    protected function nodeOpen()
    {
        $chunktag   = in_array($this->r->localName, $this->setup->chunktags);
        $nostacktag = in_array($this->r->localName, $this->setup->nostacktags);

        if ($chunktag or $nostacktag) {

            if ($this->currentChunk) {
                // Finish data for previous chunk of XML. We can't simply do
                // that when the tag ends, because we need to separately save
                // the "A" in something like "<div>A<p>B</p>C</div>"
                $this->finishChunk();
                $this->xml = '';
            }

            $this->currentChunk++;

            if ('pb' == $this->r->localName) {
                $this->newPage();
            } elseif ('milestone' == $this->r->localName) {
                $this->milestone = trim(
                    $this->r->getAttribute('unit') . '-' . $this->r->getAttribute('n'),
                    '-'
                );
            } elseif ('group' == $this->r->localName) {
                // A group of <text>s, i.e. drop the <text> we saw previously, as it
                // was just the enclosure of some inner <text> inside the <group>.
                $this->data['currentVolume'] = 0;
                $this->insidetext            = false;
            } else {
                $this->currentSection++;

                if ('text' == $this->r->localName) {

                    $this->insidetext = true;
                    $this->data['currentVolume']++;

                    if ($this->textBeforePb) {
                        $this->data['currTextStart'] = $this->page + 1;
                    } else {
                        $this->data['currTextStart'] = $this->page;
                    }
                }

                if (in_array($this->r->localName, $this->setup->structureleveltags)) {
                    $this->level++;
                }

                $this->newSection();
            }

            if ($chunktag) {
                $this->startChunk();
            }

        } elseif ('titlePart' == $this->r->localName) {
            $this->processTitlePart();
        }

        $open = $this->r->nodeOpenString();
        $this->xml .= $open;

        if (in_array($this->r->localName, $this->setup->blocktags)) {
            $this->xml .= "\n";
        }

        if ($this->r->isEmptyElement) {
            return;
        }

        if ('TEI' == $this->r->localName) {
            // Skip the enclosing <TEI>...</TEI> tags
            return;
        }

        array_push($this->prestack, $open);
        array_unshift($this->poststack, '</'.$this->r->localName.'>');
    }

    /**
     * Called when a new page is encountered.
     */
    protected function newPage()
    {
        if ($this->pageObj) {
            // Finish previous page
            $this->pageGateway->save($this->pageObj);
        }

        $this->page++;

        $this->pageObj            = $this->setup->factory->createPage();
        $this->pageObj->number    = $this->page;
        $this->pageObj->plaintext = '';
        $this->pageObj->xmlid     = $this->r->getAttribute('xml:id');
        $this->pageObj->n         = $this->r->getAttribute('n');
        $this->pageObj->rend      = $this->r->getAttribute('rend');

        if ($this->textBeforePb) {
            $this->pageObj->volume = $this->data['currentVolume'];
        } else {
            $this->pageObj->volume = $this->data['currentVolume'] + 1;
        }
    }

    /**
     * Method that's called when the input stream reaches a text
     * node or significant whitespace
     */
    protected function nodeContent()
    {
        if (!$this->currentChunk) {
            // Not in the main text -- ignore
            return;
        }
        $this->xml .= htmlspecialchars($this->r->value);
    }

    /**
     * Saves any remaining page data
     */
    protected function save()
    {
        if ($this->pageObj) {
            // Finish previous page
            $this->pageGateway->save($this->pageObj);
        }
    }

    /**
     * Method that's called when the input stream reaches a closing tag
     */
    protected function nodeClose()
    {

        $textorgroup = in_array($this->r->localName, $this->setup->nostacktags);

        if ($textorgroup) {
            // Finish data for previous chunk
            $this->finishChunk();

            // Reset variables
            $this->insidetext = false;
            $this->milestone  = null;
            $this->xml        = '';

            // Increment the chunk ID to make sure no content is overwritten
            // (this could happen, for instance, at the end of chapters)
            $this->currentChunk++;
        }

        if (in_array($this->r->localName, $this->setup->structureleveltags)) {
            $this->level--;
        }

        if ($this->currentChunk and !$textorgroup) {
            $this->xml .= '</'.$this->r->localName.'>';
            if (in_array($this->r->localName, $this->setup->blocktags)) {
                $this->xml .= "\n";
            }
        }

        array_pop($this->prestack);
        array_shift($this->poststack);
    }

    /**
     * Method which will called when a new section is encountered.
     */
    protected function newSection()
    {
        if ('text' == $this->r->localName or 'front' == $this->r->localName) {
            // <text> must not contain <head>, hence there is no title
            $title = '';
        } elseif ('titlePage' == $this->r->localName) {
            $title = $this->titlePageLabel;
        } else {
            $title = $this->titleExtractor->extractTitle($this->r->readOuterXML());
        }

        $section          = $this->setup->factory->createSection();
        $section->id      = $this->currentSection;
        $section->volume  = $this->data['currentVolume'];
        $section->title   = $title;
        $section->page    = $this->page ? $this->page : 1;
        $section->level   = $this->level;
        $section->element = $this->r->localName;
        $section->xmlid   = $this->r->getAttribute('xml:id');
        $this->sectionGateway->save($section);
    }

    /**
     * Method that's called when a new chunk is encountered. Creates a new
     * XMLChunk instance and fills it with some data.
     */
    protected function startChunk()
    {
        $chunk                    = $this->setup->factory->createXMLChunk();
        $chunk->id                = $this->currentChunk;
        $chunk->page              = $this->page;
        $chunk->section           = $this->currentSection;
        $chunk->milestone         = $this->milestone;
        $chunk->prestack          = join(' ', $this->prestack);
        $chunk->xml               = '';
        $this->chunks[$chunk->id] = $chunk;
    }

    /**
     * Method that's called when a chunk's end is encountered
     */
    protected function finishChunk()
    {
        // <pb> and <milestone> have been interpreted, now we can remove them
        $this->xml = preg_replace('#<(?:pb|milestone)\b[^>]*>#', '', $this->xml);

        $plaintext = $this->plaintextConverter->convert($this->xml);

        if ($this->pageObj) {
            $this->pageObj->plaintext .= ' '.trim($plaintext);
        }

        if (empty($this->chunks[$this->currentChunk])) {
            // This method might get called in cases where startChunk()
            // had not been called when it started.
            return;
        }

        $chunk            = $this->chunks[$this->currentChunk];
        $chunk->xml       = trim($this->xml);
        $chunk->plaintext = $plaintext;
        $chunk->poststack = join(' ', $this->poststack);
        $this->xmlChunkGateway->save($chunk);

        // Dispose of the chunk
        unset($this->chunks[$this->currentChunk]);
    }

    /**
     * {@inheritDoc}
     */
    protected function preProcessAction()
    {
        $this->volumeGateway->flush($this->setup);
        $this->sectionGateway->flush($this->setup);
        $this->pageGateway->flush($this->setup);
        $this->xmlChunkGateway->flush($this->setup);
    }

    /**
     * Callback function for occurrences of <titlePart> elements.
     *
     * This method expects each <text> to have one <titlePart>. If there is
     * more than one, subclasses may be used to filter the unwanted title(s).
     *
     * @throws \RuntimeException
     */
    protected function processTitlePart()
    {
        $title = $this->plaintextConverter->convert($this->r->readOuterXML());

        // Check for uniqueness
        if (!empty($this->data['volTitles'][$this->data['currentVolume']])) {
            throw new \RuntimeException(
                'Multiple <titlePart> elements for volume ' .
                $this->data['currentVolume'] . ":\n"
            );
        }

        $this->data['volTitles'][$this->data['currentVolume']] = true;

        $volume             = $this->setup->factory->createVolume();
        $volume->number     = $this->data['currentVolume'];
        $volume->title      = $title;
        $volume->pagenumber = $this->data['currTextStart'];

        $this->volumeGateway->save($volume);
    }
}
