<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \RuntimeException;

/**
 * Abstract base class for processing a TEI document by stream-reading it
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Indexer {

	/**
	 * Configuration
	 * @var Setup
	 */
	protected $setup;

	/**
	 * Settings
	 * @var array
	 */
	public $settings = array(
		'textbeforepb'=>true, // Does a text's <text> element enclose its first
		                      // <pb /> element? Set to false, if the order is
		                      // <pb /> ... <text>
	);

	/**
	 * Current page number
	 * @var int
	 */
	protected $page = 0;

	/**
	 * Current chunk number
	 * @var int
	 */
	protected $currentChunk = 0;

	/**
	 * Current page's unique notation / description
	 * @var int
	 */
	protected $pageNotation = null;

	/**
	 * XMLReader instance
	 * @var XMLReader
	 */
	protected $r;

	/**
	 * Method which will be called when the input stream reaches an opening
	 * tag or an empty tag.
	 */
	abstract protected function nodeOpen();

	/**
	 * Method which will be called when the input stream reaches a text
	 * node or significant whitespace.
	 */
	abstract protected function nodeContent();

	/**
	 * Method which will be called whenever the input stream reaches
	 * a closing tag.
	 */
	abstract protected function nodeClose();

	/**
	 * Constructor.
	 * @param Setup $setup
	 * @param XMLReader $xmlreader
	 * @param string $xml Input XML
	 * @param XMLReader $xmlreader
	 */
	public function __construct(Setup $setup, XMLReader $xmlreader, $xml) {

		$this->setup = $setup;

		$this->r = $xmlreader;
		$this->r->xml($xml);
	}

	/**
	 * Reads the input XML stream and calls appropriate methods when
	 * encountering opening (or empty) tags, closing tags or text
	 * content. When completed, save() is called.
	 */
	public function process() {

		$this->preProcessAction();

		while ($this->r->read()) {
			switch ($this->r->nodeType) {
				case XMLReader::ELEMENT:
					$this->nodeOpen();
					break;
				case XMLReader::END_ELEMENT:
					$this->nodeClose();
					break;
				case XMLReader::TEXT:
				case XMLReader::SIGNIFICANT_WHITESPACE:
					$this->nodeContent();
			}
		}

		$this->r->close();
		$this->save();
	}

	// @codeCoverageIgnoreStart

	/**
	 * Saves all the text data accumulated.
	 *
	 * May not be needed by subclasses. Empty default implementation.
	 */
	protected function save() {

	}

	/**
	 * Will be called right before processing starts.
	 *
	 * May not be needed by subclasses. Empty default implementation.
	 */
	protected function preProcessAction() {

	}

	// @codeCoverageIgnoreEnd

}
