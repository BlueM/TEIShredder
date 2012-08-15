<?php

namespace TEIShredder;

use \PDO;
use \PDOStatement;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * Class for extracting some tags from a TEI Lite document and for
 * transferring them to a RDBMS.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Indexer_Extractor extends Indexer {

	/**
	 * Maximum number of characters to use for the context before a match and
	 * the context behind a match.
	 * @var int
	 */
	public $contextlen = 100;

	/**
	 * String to use for marking omissions in a notation's context
	 * @var string
	 */
	public $omissionStr = "\342\200\246";

	/**
	 * Array of elements that are regarded as distinct text containers
	 * @var string[] Indexed array of element names
	 * @todo Move to setup class?
	 */
	public $containertags = array('l', 'p', 'head', 'note', 'docImprint',
	                              'byLine', 'titlePart', 'byline', 'item');

	/**
	 * Callbacks (function, method or closure) for elements.
	 * @var array Associative array containing element name=>callback pairs
	 */
	protected $elementCallbacks = array();

	/**
	 * Array that will be filled with data for all the elements found
	 * @var array Assoc. array of ID=>data pairs
	 */
	protected $tags = array();

	/**
	 * Stack of currently "open" notation tags
	 * @var array Indexed array of index numbers
	 */
	protected $notationStack = array();

	/**
	 * Index number of the current notation tag
	 * @var int
	 */
	protected $currNotatIndex = null;

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
	protected $currContainerIndex = null;

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

		// Update the elements stack, unless it's an empty element
		if (!$this->r->isEmptyElement) {
			$this->elementStack[] = $this->r->localName;
		}

		if ('pb' == $this->r->localName) {
			// New page: increase page number, save element and return
			$this->page ++;
			$this->newElement();
			return;
		}

		// Any other case: save element
		$this->newElement();

		if ('lb' == $this->r->localName or
		    'milestone' == $this->r->localName) {
			// Linebreak -- simply insert space
			if (isset($this->containers[$this->currContainerIndex])) {
				$this->containers[$this->currContainerIndex] .= ' ';
			}
			return;
		}

		if (in_array($this->r->localName, $this->containertags)) {
			$index ++;
			$this->containerStack[] = $index;
			$this->currContainerIndex = end($this->containerStack);

			if ('note' == $this->r->localName) {
				if ($this->r->getAttribute('resp')) {
					// Scholarly annotation
					$this->containerTypes[$index] = 'annotation';
				} else {
					// Note by author: footnote or marginalia text
					$this->containerTypes[$index] = 'margin_footnote';
				}
			} elseif (in_array('figure', $this->elementStack)) {
				$this->containerTypes[$index] = 'figure';
			} else {
				$this->containerTypes[$index] = $this->r->localName;
			}
		}

		if ($this->r->localName != 'rs') {
			return;
		}

		// If we reach this point, we are inside a container tag
		$index ++;
		$this->notationStack[] = $index;
		$this->currNotatIndex = end($this->notationStack);

		// @codeCoverageIgnoreStart
		if (!$this->currContainerIndex) {
			throw new RuntimeException('No current container for “'.$this->r->extractPlaintextContent()."”\n");
		}
		// @codeCoverageIgnoreEnd

		if (empty($this->containers[$this->currContainerIndex])) {
			$this->containers[$this->currContainerIndex] = '';
		}
		$this->containers[$this->currContainerIndex] .= '<'.$this->currNotatIndex.'>';

		$this->tags[$index] = array(
			'id'=>$index,
			'xmlid'=>$this->r->getAttribute('xml:id'),
			'container'=>$this->currContainerIndex,
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

		if (empty($this->containers[$this->currContainerIndex])) {
			$this->containers[$this->currContainerIndex] = '';
		}

		$this->containers[$this->currContainerIndex] .= $this->r->value;
	}

	/**
	 * Method that's called when the input stream reaches a closing tag
	 */
	protected function nodeClose() {

		// Update the elements stack
		array_pop($this->elementStack);

		if (in_array($this->r->localName, $this->containertags)) {
			array_pop($this->containerStack);
			$this->currContainerIndex = end($this->containerStack);
		}

		if ($this->r->localName == 'rs') {
			// Closing notation tag
			$this->containers[$this->currContainerIndex] .= '</'.$this->currNotatIndex.'>';
			array_pop($this->notationStack);
			if (count($this->notationStack)) {
				$this->currNotatIndex = end($this->notationStack);
			} else {
				$this->currNotatIndex = null;
			}
		}

	}

	/**
	 * Saves all the named entities' data accumulated before (plus some
	 * additional data, such as some context string) to the database.
	 */
	protected function save() {

		$sth = $this->setup->database->prepare(
			'INSERT INTO '.$this->setup->prefix.'entity'.
			' (xmlid, page, chunk, domain, key, notation, context, container,'.
			' notationhash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		foreach (array_values($this->tags) as $tag) {

			if ('annotation' == $this->containerTypes[$tag['container']]) {
				// Do not include anything inside scholarly annotations.
				continue;
			}

			$context = $this->containers[$tag['container']];

			// Insert marker for "own" notation, then remove other notations
			$context = preg_replace(
				'#</?\d+>#',
				'',
				str_replace(array('<'.$tag['id'].'>', '</'.$tag['id'].'>'), '###', $context)
			);

			// Convert the context to plaintext
			$context = call_user_func($this->setup->plaintextCallback, $context);

			$context = trim(preg_replace('#\s+#u', ' ', $context));

			// Limit the amount of context
			@list($before, $notation, $after) = explode('###', $context);

			if (mb_strlen($before) >= $this->contextlen) {
				if (false !== $pos = strrpos($before, ' ', -$this->contextlen)) {
					// Shorten "before" text
					$before = $this->omissionStr.substr($before, $pos);
				}
			}

			if (mb_strlen($after) >= $this->contextlen) {
				if (false !== $pos = strpos($after, ' ', $this->contextlen)) {
					// Shorten "behind" text
					$after = substr($after, 0, $pos).$this->omissionStr;
				}
			}

			$context = "$before<$>$after";

			for ($i = 0, $ii = count($tag['key']); $i < $ii; $i ++) {
				// Each entry in array $tag['key'] points to
				// a different target. If there are multiple entries,
				// we have to add multiple records to support links
				// with multiple targets
				$sth->execute(
					array(
						$tag['xmlid'],
						$tag['page'],
						$tag['chunk'],
						$tag['domain'],
						$tag['key'][$i],
						$notation,
						$context,
						$this->containerTypes[$tag['container']],
						// For finding specific notations, we save a hash of the
						// lowercased notation. For our purposes, just using the
						// first 8 chars should be OK, as there is very little
						// danger that collisions occur.
						substr(md5(mb_convert_case(trim($notation), MB_CASE_LOWER)), 0, 8),
					)
				);
			}
		}
	}

	/**
	 * Records occurrences of any elements that have an xml:id attribute.
	 */
	protected function newElement() {

		if ('' == $xmlid = $this->r->getAttribute('xml:id')) {
			// No xml:id attribute >> nothing to do
			return;
		}

		// Add attributes we need for various purposes
		if (isset($this->elementCallbacks[$this->r->localName])) {
			$attrs = call_user_func($this->elementCallbacks[$this->r->localName], $this->r);
		} else {
			$attrs = array('attrn'=>'', 'attrtargetend'=>'', 'data'=>'');
		}

		$e = new Element($this->setup);
		$e->xmlid = $xmlid;
		$e->element = $this->r->localName;
		$e->page = $this->page;
		$e->chunk = $this->currentChunk;
		$e->attrn = $attrs['attrn'];
		$e->attrtargetend = $attrs['attrtargetend'];
		$e->data = $attrs['data'];
		$e->save();
	}

	/**
	 * Defines a callback for specific elements.
	 *
	 * This callback is expected to return an array with three keys: "data" is arbitrary
	 * data to index, "attrn" is the value of the "n" attribute (if present) and "attrtargetend"
	 * (the value of the @targetEnd attribute). The callback is given one argument, which is
	 * an XMLReader instance of the node.
	 * @param string $element
	 * @param mixed $callback
	 * @throws InvalidArgumentException
	 */
	public function setElementCallback($element, $callback) {
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Argument 2 must be a function name, method (as array) '.
					                           'or Closure. Given: '.print_r($callback, true));
		}
		$this->elementCallbacks[$element] = $callback;
	}

	/**
	 * Setup method that will be called right before processing starts.
	 */
	protected function preProcessAction() {
		$db = $this->setup->database;
		$prefix = $this->setup->prefix;
		Element::flush($this->setup);
		$db->exec('DELETE FROM '.$prefix.'entity');
	}

}
