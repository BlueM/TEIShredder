<?php

namespace TEIShredder;

use \PDO;
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

			if (in_array('figure', $this->elementStack)) {
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
				$entity = new NamedEntity($this->setup);
				$entity->xmlid = $tag['xmlid'];
				$entity->page = $tag['page'];
				$entity->domain = $tag['domain'];
				$entity->identifier = $tag['key'][$i];
				$entity->contextstart = $before;
				$entity->notation = $notation;
				$entity->contextend = $after;
				$entity->container = $this->containerTypes[$tag['container']];
				$entity->chunk = $tag['chunk'];
				NamedEntityGateway::save($this->setup, $entity);
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
		ElementGateway::save($this->setup, $e);
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
		ElementGateway::flush($this->setup);
		NamedEntityGateway::flush($this->setup);
	}

}
