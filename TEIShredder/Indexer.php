<?php

/**
 * Base class for processing a TEI document by stream-reading it
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link http://www.sandrart.net/
 * @version SVN: $Id: TEIProcessor.php 1289 2012-03-20 15:17:53Z cb $
 */
abstract class TEIShredder_Indexer {

	/**
	 * Configuration
	 * @var TEIShredder_Setup
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
	 * Array of element types / tag names that mark the beginning of
	 * a new chunk of text (can be either empty or non-empty elements).
	 * @var array Indexed array of element names
	 */
	static $chunktags = array('pb', 'milestone', 'div', 'front', 'body');

	/**
	 * Array of element types / tag names that mark the beginning of
	 * a new chunk of text, but which should not be indexed separately.
	 * (Basically, these are more important for detecting text chunks'
	 * ends than their beginning.)
	 */
	static $nostacktags = array('text', 'group');

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
	 * @var TEIShredder_XMLReader
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
	 * Constructor
	 * @param TEIShredder_Setup $setup
	 * @param string $path Filesystem path to the source XML file
	 * @param Closure $modify [optional] Closure that will be called with
	 *                        the XML as argument to perform XML pre-processing.
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function __construct(TEIShredder_Setup $setup, $path, Closure $modify = null) {

		if (!file_exists($path)) {
			throw new InvalidArgumentException("Invalid path $path");
		}

		$this->setup = $setup;

		if (!file_exists($path) or
		    !is_readable($path)) {
			throw new InvalidArgumentException("There is no readable file at $path");
		}
		$xml = file_get_contents($path);

		if ($modify) {
			$xml = $modify($xml);
		}

		// Create the XML reader
		$this->r = new TEIShredder_XMLReader;
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

	/**
	 * Saves all the text data accumulated. May not be needed by
	 * all subclasses. This default implementation does nothing.
	 */
	protected function save() {

	}

	/**
	 * Will be called right before processing starts. Can be used to
	 * check certain conditions (should throw an exception if it fails)
	 * or to perform initialization steps. This default implementation is empty.
	 */
	protected function preProcessAction() {

	}

}
