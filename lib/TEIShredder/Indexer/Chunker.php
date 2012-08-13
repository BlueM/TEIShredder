<?php

namespace TEIShredder;

use \RuntimeException;

/**
 * Class for splitting a TEI document in well-formed XML chunks.
 *
 * Known shortcomings: Cannot handle nested <group> tags.
 * Unknown shortcomings: Surely lots!
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Indexer_Chunker extends Indexer {

	/**
	 * String that will be used as title for <titlePage> sections
	 * @var string
	 */
	public $titlePageLabel = 'Title page';

	/**
	 * Array of tags that are yet unclosed at the point
	 * in the text where this chunk starts.
	 * @var array Indexed array
	 */
	protected $prestack = array();

	/**
	 * Array of tags that are yet unclosed at the point in the text
	 * where this chunk ends
	 * @var array Indexed array
	 */
	protected $poststack = array();

	/**
	 * Flag: are we inside of a chunk tag?
	 * @var bool
	 */
	protected $insidetext = false;

	/**
	 * Array for keeping track of various internal variables.
	 * @var array
	 */
	protected $data = array(
		'currTextStart'=>0,   // Page number at which the current <text> started
		'currentVolume'=>0,   // Current volume number
		'volTitles'=>array(), // Titles of the volumes
	);

	/**
	 * ID of section currently being processed
	 * @var int
	 */
	protected $currentSection = 0;

	/**
	 * Array of element types / tag names that are regarded as block
	 * elements, i.e. after which whitespace is inserted
	 * @var array Indexed array of element names
	 */
	protected $blocktags = array('p', 'pb', 'div', 'milestone', 'figure',
	                             'text', 'body', 'argument', 'lb', 'head');

	/**
	 * Variable used to collect current chunk's contents
	 * @var string
	 */
	protected $xml = null;

	/**
	 * Current column
	 * @var string
	 */
	protected $column = null;

	/**
	 * Text structure level. Only named sections (those with a <head> are
	 * counted, as those without sometimes are only present to comply with TEI.
	 * @var int
	 */
	protected $level = 0;

	/**
	 * Contains pagenumber=>plaintext pairs
	 * @var array
	 */
	protected $plaintext = array(0=>'');

	/**
	 * Method that's called when the input stream reaches an opening
	 * tag (or an empty tag)
	 */
	protected function nodeOpen() {

		if (in_array($this->r->localName, self::$chunktags) or
			in_array($this->r->localName, self::$nostacktags)) {

			if (in_array($this->r->localName, self::$chunktags)) {
				$this->insidetext = true;
			}

			if ($this->currentChunk) {
				// Finish data for previous chunk of XML
				// We can't simply do that when the tag ends,
				// because we need to separately save the
				// "A" in something like "<div>A<p>B</p>C</div>"
				$this->finishChunk();

				// Empty the XML variable
				$this->xml = '';
			}

			$this->currentChunk ++;

			if ('pb' == $this->r->localName) {
				$this->registerNewPage();
			} elseif ('milestone' == $this->r->localName) {
				$this->column = $this->r->getAttribute('unit');
			} elseif ('div' == $this->r->localName or
					  'text' == $this->r->localName or
					  'titlePage' == $this->r->localName or
					  'front' == $this->r->localName) {
				$this->currentSection ++;

				if ('text' == $this->r->localName) {
					$this->data['currentVolume'] ++;

					if ($this->settings['textbeforepb']) {
						$this->data['currTextStart'] = $this->page + 1;
					} else {
						$this->data['currTextStart'] = $this->page;
					}
				}

				if ('div' == $this->r->localName or
				    'titlePage' == $this->r->localName) {
					$this->level ++;
				}

				$this->startSection();

			} elseif ('group' == $this->r->localName) {
				// A group of <text>s, i.e. forget the <text> we saw previously,
				// as it was just the enclosure of some inner <text> inside the
				// <group>.
				$this->data['currentVolume'] = 0;
			}

			if (in_array($this->r->localName, self::$chunktags)) {
				$this->startChunk();
			}
		} elseif ('titlePart' == $this->r->localName) {
			$this->processTitlePart();
		}

		if (!$this->insidetext) {
			// Not in the main text -- ignore
			return;
		}

		$string = $this->r->nodeOpenString();

		$this->xml .= $string;
		if (in_array($this->r->localName, $this->blocktags)) {
			$this->xml .= "\n";
		}

		if (!$this->r->isEmptyElement and
			!in_array($this->r->localName, self::$nostacktags)) {
			// Note: in the next line, we record the opening element without
			// @xml:id attribute, as when outputting several chunks of XML on
			// one HTML page, there could be easily be a conflict due to
			// non-unique @id attributes. And we dont't need it anyway, as
			// the pre-stack is only for wellformedness.
			array_push($this->prestack, $this->r->nodeOpenString(true));
			array_unshift($this->poststack, '</'.$this->r->localName.'>');
		}
	}

	/**
	 * Method that's called when the input stream reaches a text
	 * node or significant whitespace
	 */
	protected function nodeContent() {
		if (!$this->currentChunk) {
			// Not in the main text -- ignore
			return;
		}
		$this->xml .= htmlspecialchars($this->r->value);
	}

	/**
	 * Called when a new page (<pb/> tag) is encountered.
	 */
	protected function registerNewPage() {

		$this->page ++;
		$this->plaintext[$this->page] = '';

		$db = $this->setup->database;
		$db->exec(
			'INSERT INTO '.$this->setup->prefix.'page'.'
			 (page, xmlid, volume, n, rend, facs)
			 VALUES('. $db->quote($this->page).',
			        '. $db->quote($this->r->getAttribute('xml:id')).',
			        '. $db->quote($this->data['currentVolume']).',
			        '. $db->quote($this->r->getAttribute('n')).',
			        '. $db->quote($this->r->getAttribute('rend')).',
			        '. $db->quote($this->r->getAttribute('facs')).')'
		);

	}

	/**
	 * Method that's called when the input stream reaches a closing tag
	 */
	protected function nodeClose() {

		if ($this->insidetext and
			in_array($this->r->localName, self::$nostacktags)) {
			// Finish data for previous chunk
			$this->finishChunk();

			// Reset variables
			$this->insidetext = false;
			$this->column = null;
			$this->xml = '';

			if ($this->currentChunk) {
				// Increment the chunk ID to make sure no content is overwritten
				// (this could happen, for instance, at the end of chapters)
				$this->currentChunk ++;
			}
		}

		if ('div' == $this->r->localName or
		    'titlePage' == $this->r->localName) {
			$this->level --;
		}

		if ($this->currentChunk and
			!in_array($this->r->localName, self::$nostacktags)) {
			$this->xml .= '</'.$this->r->localName.'>';
			if (in_array($this->r->localName, $this->blocktags)) {
				$this->xml .= "\n";
			}
		}

		if (!in_array($this->r->localName, self::$nostacktags)) {
			array_pop($this->prestack);
			array_shift($this->poststack);
		}
	}

	/**
	 * Method which will called when a new section is encountered.
	 */
	protected function startSection() {

		if ($this->r->getAttribute('noindex')) {
			// Should not be indexed
			return;
		}

		if ('text'  == $this->r->localName or
		    'front' == $this->r->localName) {
			// <text> must not contain <head> directly
			$title = '';
		} elseif ('titlePage' == $this->r->localName) {
			$title = $this->titlePageLabel;
		} else {
			$title = call_user_func($this->setup->titleCallback, $this->r->readOuterXML());
		}

		$level = $this->level;

		$db = $this->setup->database;

		$db->exec(sprintf(
			'INSERT INTO %sstructure (id, volume, title, page, level, element, xmlid) '.
		    'VALUES (%d, %d, %s, %d, %d, %s, %s)',
			$this->setup->prefix,
			$this->currentSection,
			$this->data['currentVolume'],
			$db->quote($title),
			$this->page ? $this->page : 1,
			$level,
			$db->quote($this->r->localName),
			$db->quote($this->r->getAttribute('xml:id'))
		));
	}

	/**
	 * Method that's called when a new chunk is encountered
	 */
	protected function startChunk() {
		$db = $this->setup->database;
		$prestackstr = join(' ', $this->prestack);
		$db->exec(
			'INSERT INTO '.$this->setup->prefix.'xmlchunk'.'
			 (id, volume, page, section, column, prestack)
			 VALUES ('. $db->quote($this->currentChunk).',
					 '. $db->quote($this->data['currentVolume']).',
					 '. $db->quote($this->page).',
					 '. $db->quote($this->currentSection).',
					 '. $db->quote($this->column).',
					 '. $db->quote($prestackstr).')');
	}

	/**
	 * Method that's called when a chunk's end is encountered
	 */
	protected function finishChunk() {

		// <pb> and <milestone> have been interpreted, now we can remove them
		$this->xml = preg_replace('#<(?:pb|milestone)\b[^>]*>#', '', $this->xml);

		$plaintext = call_user_func($this->setup->plaintextCallback, $this->xml);

		$db = $this->setup->database;
		$db->exec(
			'UPDATE '.$this->setup->prefix.'xmlchunk'.'
			 SET xml = '.$db->quote(trim($this->xml)).',
			     plaintext = '.$db->quote($plaintext).',
			     poststack = '.$db->quote(join(' ', $this->poststack)).'
			 WHERE id = '.$db->quote($this->currentChunk));

		if ($plaintext) {
			$this->plaintext[$this->page] .= ' '.trim($plaintext);
		}
	}

	/**
	 * Saves accumulated data
	 */
	protected function save() {
		$db = $this->setup->database;
		$prefix = $this->setup->prefix;
		$sth = $db->prepare(
			"UPDATE ".$prefix.'page'." SET plaintext = ? WHERE page = ?"
		);
		foreach ($this->plaintext as $page=>$plaintext) {
			$sth->execute(array($plaintext, trim($page)));
		}
	}

	/**
	 * Will be called right before processing starts. Can be used to
	 * check certain conditions (should throw an exception if it fails)
	 * or to perform initialization steps.
	 */
	protected function preProcessAction() {
		// Empty the tables
		$db = $this->setup->database;
		$prefix = $this->setup->prefix;
		$db->exec('DELETE FROM '.$prefix.'page');
		$db->exec("DELETE FROM ".$prefix.'xmlchunk');
		$db->exec('DELETE FROM '.$prefix.'structure');
		$db->exec('DELETE FROM '.$prefix.'volume');
	}

	/**
	 * #todo
	 * @throws RuntimeException
	 */
	protected function processTitlePart() {

		if ($this->r->getAttribute('noindex')) {
			// Should not be indexed
			return;
		}

		if ($this->r->getAttribute('type') and
			'main' != $this->r->getAttribute('type')) {
			// Not a main title
			return;
		}

		$title = call_user_func(
			$this->setup->plaintextCallback,
			$this->r->readOuterXML()
		);

		// Check for uniqueness
		if (!empty($this->data['volTitles'][$this->data['currentVolume']])) {
			throw new RuntimeException('Multiple <titlePart>...</titlePart>s for volume '.$this->data['currentVolume'].":\n");
		}

		$this->data['volTitles'][$this->data['currentVolume']] = true;

		$db = $this->setup->database;
		$db->exec(
			'INSERT INTO '.$this->setup->prefix.'volume'.' (number, title, pagenum)
			 VALUES('.$db->quote($this->data['currentVolume']).',
			 '.$db->quote($title).', '.$this->data['currTextStart'].')'
		);

	}
}
