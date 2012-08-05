<?php

/**
 * Class for extracting some tags from a TEI Lite document and for
 * transferring them to a RDBMS.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TEIShredder_Indexer_Extractor extends TEIShredder_Indexer {

	/**
	 * Maximum number of characters to use for the context before a match and
	 * the context behind a match.
	 * #todo Move to setup class
	 * @var int
	 */
	public $contextlen = 100;

	/**
	 * String to use for marking omissions in a notation's context
	 * #todo Move to setup class
	 * @var string
	 */
	public $omissionStr = "\342\200\246";

	/**
	 * #todo
	 * @var $elementCallbacks
	 */
	public $elementCallbacks = array();

	/**
	 * Keeps track of whether we are currently inside a <figure> tag or
	 * not. If it's > 0, we are.
	 * @var int
	 */
	protected $insideFigure = 0;

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
	 * Indexed array of elements that are currently open
	 * @var string
	 */
	protected $elementStack = array();

	/**
	 * Array that contains the text chunks in which the notations occur
	 * @var array Indexed array
	 */
	protected $containers = array();

	/**
	 * Array that contains the containers' xml:id attribute values
	 * @var array Indexed array
	 */
	protected $containerids = array();

	/**
	 * Indexed array that contains the container name / description,
	 * such as the tag name or another arbitrary name given
	 * @var array
	 */
	protected $containerTypes = array();

	/**
	 * Array of elements that are regarded as distinct text containers
	 * @var string[] Indexed array of element names
	 * #todo Move to setup class
	 */
	static $containertags = array('l', 'p', 'head', 'note', 'docImprint',
	                              'byLine', 'titlePart', 'byline', 'item');

	/**
	 * Text that's inside these tags will not be included in the
	 * notations' context strings
	 * @var string[]
	 */
	static $ignorabletags = array('sic', 'del', 'orig');

	/**
	 * The tag that we're interested in; the one whose occurrences and
	 * attributes should be extracted
	 * @var string
	 */
	static $notationtag = 'rs';

	/**
	 * Method that's called when the readerstream reaches an opening or empty tag.
	 * @return mixed
	 * @throws RuntimeException
	 */
	protected function nodeOpen() {

		static $index = 0;

		// Keep track of the chunk number (must match the way the the chunk
		// number is determined / incremented in TETool_TEIProcessor_Chunker
		if (in_array($this->r->localName, self::$chunktags) or
			in_array($this->r->localName, self::$nostacktags)) {
			$this->currentChunk ++;
		}

		// Update the elements stack
		$this->elementStack[] = $this->r->localName;

		if ('pb' == $this->r->localName) {
			// Increase the page number
			$this->page ++;
		}

		// Save the element's xml:id attribute (if it exists)
		$this->saveId();

		if ('pb' == $this->r->localName) {
			// Now that the xml:id attribute was saved, we don't
			// need any further processing steps for <pb />
			return;
		}

		if ('lb' == $this->r->localName or
		    'milestone' == $this->r->localName) {
			// Linebreak -- simply insert space
			if (isset($this->containers[$this->currContainerIndex])) {
				$this->containers[$this->currContainerIndex] .= ' ';
			}
			return;
		}

		if ('figure' == $this->r->localName) {
			$this->insideFigure ++;
		}

		if (in_array($this->r->localName, self::$containertags)) {
			$index ++;
			$this->containerStack[] = $index;
			$this->currContainerIndex = end($this->containerStack);

			$xmlid = $this->r->getAttribute('xml:id');

			if ($xmlid) {
				$this->containerids[$this->currContainerIndex] = $xmlid;
			} else {
				$this->containerids[$this->currContainerIndex] = '';
			}

			if ('note' == $this->r->localName) {
				if ($this->r->getAttribute('resp')) {
					// Scholarly annotation
					$this->containerTypes[$index] = 'annotation';
				} else {
					// Note by author: footnote or marginalia text
					$this->containerTypes[$index] = 'margin_footnote';
				}
			} elseif ($this->insideFigure) {
				$this->containerTypes[$index] = 'figure';
			} else {
				$this->containerTypes[$index] = $this->r->localName;
			}
		}

		if ($this->r->localName != self::$notationtag) {
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
			'object'=>explode(' ', $this->r->getAttribute('key')), // Supports multiple targets
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

		$tag = end($this->elementStack);
		if (in_array($tag, self::$ignorabletags)) {
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

		if (in_array($this->r->localName, self::$containertags)) {
			array_pop($this->containerStack);
			$this->currContainerIndex = end($this->containerStack);
		}

		if ($this->r->localName == self::$notationtag) {
			// Closing notation tag
			$this->containers[$this->currContainerIndex] .= '</'.$this->currNotatIndex.'>';
			array_pop($this->notationStack);
			if (count($this->notationStack)) {
				$this->currNotatIndex = end($this->notationStack);
			} else {
				$this->currNotatIndex = null;
			}
		}

		if ('figure' == $this->r->localName) {
			$this->insideFigure --;
		}
	}

	/**
	 * Saves all the tags' data accumulated before (plus some additional
	 * data, such as some context string) to the database.
	 */
	protected function save() {

		$sth = $this->setup->database->prepare(
			'INSERT INTO '.$this->setup->prefix.'notation'.
			' (xmlid, page, chunk, domain, object, notation, context, container,'.
			' containerid, notationhash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		foreach (array_values($this->tags) as $tag) {

			if ('annotation' == $this->containerTypes[$tag['container']]) {
				// Do not include anything inside scholarly annotations.
				continue;
			}

			$context = $this->containers[$tag['container']];

			// Insert marker for "own" notation, then remove other notations
			$id = $tag['id'];
			$context = str_replace("<$id>", '###', $context);
			$context = str_replace("</$id>", '###', $context);
			$context = preg_replace('#</?\d+>#', '', $context);

			// Convert the context to plaintext
			$context = call_user_func($this->setup->plaintextCallback, $context);

			// Limit the amount of context
			@list($before, $notation, $behind) = explode('###', $context);

			if (mb_strlen($before) >= $this->contextlen) {
				if (false !== $pos = strrpos($before, ' ', -$this->contextlen)) {
					// Shorten "before" text
					$before = $this->omissionStr.substr($before, $pos);
				}
			}

			if (mb_strlen($behind) >= $this->contextlen) {
				if (false !== $pos = strpos($behind, ' ', $this->contextlen)) {
					// Shorten "behind" text
					$behind = substr($behind, 0, $pos).$this->omissionStr;
				}
			}

			$context = "$before<$>$behind";
			$context = trim(preg_replace('#\s+#u', ' ', $context));

			for ($i = 0, $ii = count($tag['object']); $i < $ii; $i ++) {
				// Each entry in array $tag['object'] points to
				// a different target. If there are multiple entries,
				// we have to add multiple records to support links
				// with multiple targets
				$sth->execute(
					array(
						$tag['xmlid'],
						$tag['page'],
						$tag['chunk'],
						$tag['domain'],
						(int)$tag['object'][$i],
						preg_replace('#\s+#', ' ', $notation),
						$context,
						$this->containerTypes[$tag['container']],
						$this->containerids[$tag['container']],
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
	 * This method serves different purposes than save() -- it does nothing
	 * but record which xml:id attribute occurs on which page in which text
	 * chunk.
	 */
	protected function saveId() {

		static $sth = null;

		if ('' == $idattr = $this->r->getAttribute('xml:id')) {
			// No xml:id attribute >> nothing to do
			return;
		}

		// Add attributes we need for various purposes
		if (isset($this->elementCallbacks[$this->r->localName])) {
			$tmp = call_user_func($this->elementCallbacks[$this->r->localName], $this->r);
			$n = $tmp['n'];
			$targetend = $tmp['targetend'];
			$data = $tmp['data'];
		} else {
			$n = $targetend = $data = '';
		}

		if (null === $sth) {
			$sth = $this->setup->database->prepare(
				'INSERT INTO '.$this->setup->prefix.'element'.
				' (xmlid, element, page, chunk, attrn, attrtargetend, data)'.
				' VALUES (?, ?, ?, ?, ?, ?, ?)'
			);
		}

		$sth->execute(array(
			$idattr,
			$this->r->localName,
			(int)$this->page,
			$this->currentChunk,
			$n,
			$targetend,
			$data
		));
	}

	/**
	 * Defines a callback for specific elements.
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
		$db->exec("DELETE FROM ".$prefix.'element');
		$db->exec('DELETE FROM '.$prefix.'notation');
	}

}
