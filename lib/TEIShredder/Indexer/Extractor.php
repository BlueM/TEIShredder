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

namespace TEIShredder;

use \PDO;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * Class for extracting some tags from a TEI Lite document and for
 * transferring them to a RDBMS.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Indexer_Extractor extends Indexer {

	/**
	 * Array of elements that are regarded as distinct text containers
	 * @var string[] Indexed array of element names
	 * @todo Move to setup class?
	 */
	public $containertags = array('l', 'p', 'head', 'note', 'docImprint',
	                              'byLine', 'titlePart', 'byline', 'item');

	/**
	 * Array that will be filled with data for all the elements found
	 * @var array Assoc. array of ID=>data pairs
	 */
	protected $tags = array();

	/**
	 * Stack of currently "open" entity tags
	 * @var array Indexed array of index numbers
	 */
	protected $entityStack = array();

	/**
	 * Stack of currently "open" container tags, which enclose the
	 * current point in the XML stream
	 * @var array Indexed array of index numbers
	 */
	protected $containerStack = array();

	/**
	 * Index number of the current container tag
	 * @var int
	 */
	protected $containerIndex = 0;

	/**
	 * Indexed array of elements that are currently open. Will contain
	 * something like array('text', 'body', 'div', 'p')
	 * @var string
	 */
	protected $elementStack = array();

	/**
	 * Array that contains the text chunks in which the notations occur
	 * @var array Indexed array
	 */
	protected $containers = array();

	/**
	 * Indexed array that contains the container name/description,
	 * such as the tag name or another arbitrary name.
	 * @var array
	 */
	protected $containerTypes = array();

	#todo
	protected $gateways = array();

	/**
	 * Constructor.
	 * @param Setup $setup
	 * @param XMLReader $xmlreader
	 * @param string $xml Input XML
	 * @param XMLReader $xmlreader
	 */
	public function __construct(Setup $setup, XMLReader $xmlreader, $xml) {
		parent::__construct($setup, $xmlreader, $xml);
		$this->gateways['entity'] = $setup->factory->createNamedEntityGateway();
		$this->gateways['element'] = $setup->factory->createElementGateway();
	}

	/**
	 * Method that's called when the stream reaches an opening or empty tag.
	 * @return mixed
	 * @throws RuntimeException
	 */
	protected function nodeOpen() {

		// $index is just a counter (not used outside) for ensuring unique container IDs
		static $index = 0;

		// Keep track of the chunk number
		if (in_array($this->r->localName, $this->setup->chunktags) or
			in_array($this->r->localName, $this->setup->nostacktags)) {
			$this->currentChunk ++;
		}

		// New page: increase page number, save element and return
		if ('pb' == $this->r->localName) {
			$this->page ++;
			$this->newElement();
			return;
		}

		// In case of <lb/> or <milestone/>, add space to the enclosing
		// container (if any), but don't do anything else, i.e.: return.
		if ('lb' == $this->r->localName or
		    'milestone' == $this->r->localName) {
			if (isset($this->containers[$this->containerIndex])) {
				$this->containers[$this->containerIndex] .= ' ';
			}
			return;
		}

		// Update the elements stack, unless it's an empty element
		if (!$this->r->isEmptyElement) {
			$this->elementStack[] = $this->r->localName;
		}

		// Create a new element
		$this->newElement();

		if (in_array($this->r->localName, $this->containertags)) {
			$index ++;
			$this->containerStack[] = $index;
			$this->containerIndex = $index;

			if (in_array('figure', $this->elementStack)) {
				$this->containerTypes[$index] = 'figure';
			} else {
				$this->containerTypes[$index] = $this->r->localName;
			}
		}

		if ($this->r->localName != 'rs') {
			return;
		}

		// If we reach this point, it's a named entity (<rs>...</rs> tag)
		$index ++;
		$this->entityStack[] = $index;

		if (empty($this->containers[$this->containerIndex])) {
			$this->containers[$this->containerIndex] = '';
		}
		$this->containers[$this->containerIndex] .= '<'.$index.'>';

		$this->tags[$index] = array(
			'id'=>$index,
			'xmlid'=>$this->r->getAttribute('xml:id'),
			'container'=>$this->containerIndex,
			'tag'=>$this->r->nodeOpenString(),
			'page'=>$this->page,
			'domain'=>$this->r->getAttribute('type'),
			'key'=>explode(' ', $this->r->getAttribute('key')), // Supports multiple targets
			'chunk'=>$this->currentChunk,
		);
	}

	/**
	 * Method that's called when the input stream reaches a text
	 * node or significant whitespace
	 */
	protected function nodeContent() {

		if (array() == $this->containerStack) {
			// Not inside a container -- ignore
			return;
		}

		if (in_array(end($this->elementStack), $this->setup->ignorabletags)) {
			return;
		}

		if (empty($this->containers[$this->containerIndex])) {
			$this->containers[$this->containerIndex] = '';
		}

		$this->containers[$this->containerIndex] .= $this->r->value;
	}

	/**
	 * Method that's called when the input stream reaches a closing tag
	 */
	protected function nodeClose() {

		// Remove the closed element from the element stack
		array_pop($this->elementStack);

		if (in_array($this->r->localName, $this->containertags)) {
			array_pop($this->containerStack);
			$this->containerIndex = end($this->containerStack);
		}

		if ($this->r->localName == 'rs') {
			// Closing entity tag
			$index = array_pop($this->entityStack);
			$this->containers[$this->containerIndex] .= '</'.$index.'>';
		}

	}

	/**
	 * Saves all the named entities' data accumulated before (plus some
	 * additional data, such as some context string) to the database.
	 */
	protected function save() {

		foreach (array_values($this->tags) as $tag) {

			$context = $this->containers[$tag['container']];

			// Insert marker for "own" notation, then remove other notations
			$context = preg_replace(
				'#</?\d+>#',
				'',
				str_replace(array('<'.$tag['id'].'>', '</'.$tag['id'].'>'), '###', $context)
			);

			// Convert the context to plaintext. Therefore, escape special chars
			// in the plaintext, as the plaintext conversion code should expect XML.
			$context = call_user_func($this->setup->plaintextCallback, htmlspecialchars($context));

			$context = trim(preg_replace('#\s+#u', ' ', $context));

			// Limit the amount of context
			@list($before, $notation, $after) = explode('###', $context);

			if (!$notation) {
				// If there's not textual content, provide at least an indicator
				$notation = '[…]';
			}

			for ($i = 0, $ii = count($tag['key']); $i < $ii; $i ++) {
				// Each entry in array $tag['key'] points to
				// a different target. If there are multiple entries,
				// we have to add multiple records to support links
				// with multiple targets
				$entity = $this->setup->factory->createNamedEntity($this->setup);
				$entity->xmlid = $tag['xmlid'];
				$entity->page = $tag['page'];
				$entity->domain = $tag['domain'];
				$entity->identifier = $tag['key'][$i];
				$entity->contextstart = $before;
				$entity->notation = $notation;
				$entity->contextend = $after;
				$entity->container = $this->containerTypes[$tag['container']];
				$entity->chunk = $tag['chunk'];
				$this->gateways['entity']->save($entity);
			}
		}
	}

	/**
	 * Records occurrences of any elements that have an xml:id attribute.
	 */
	protected function newElement() {

		if (null == $xmlid = $this->r->getAttribute('xml:id')) {
			// No xml:id attribute >> Ignore
			return;
		}

		$e = $this->setup->factory->createElement($this->setup);
		$e->xmlid = $xmlid;
		$e->element = $this->r->localName;
		$e->page = $this->page;
		$e->chunk = $this->currentChunk;
		$this->gateways['element']->save($e);
	}

	/**
	 * Setup method that will be called right before processing starts.
	 */
	protected function preProcessAction() {
		$this->gateways['element']->flush($this->setup);
		$this->gateways['entity']->flush($this->setup);
	}

}
