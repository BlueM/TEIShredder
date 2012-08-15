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
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Indexer_Chunker extends Indexer {

	/**
	 * String that will be used as title for <titlePage> sections
	 * @var string
	 */
	public $titlePageLabel = 'Title page';

	/**
	 * Array of tags that are yet unclosed at the point
	 * in the text where this chunk starics.
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
	 * Contains XMLChunk instances
	 * @var array Associative array containing id=>XMLChunk instance pairs
	 */
	protected $chunks = array();

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
	 * counted, as those without sometimes are only present to comply with TEI. @todo comment?
	 * @var int
	 */
	protected $level = 0;

	/**
	 * The current Page object
	 * @var Page
	 */
	protected $pageObj;

	/**
	 * Method that's called when the input stream reaches an opening
	 * tag (or an empty tag)
	 */
	protected function nodeOpen() {

		$chunktag   = in_array($this->r->localName, $this->setup->chunktags);
		$nostacktag = in_array($this->r->localName, $this->setup->nostacktags);
		$sectiontag = in_array($this->r->localName, $this->setup->sectiontags);

		if ($chunktag or
			$nostacktag) {

			if ($chunktag) {
				$this->insidetext = true;
			}

			if ($this->currentChunk) {
				// Finish data for previous chunk of XML. We can't simply do that when
				// the tag ends, because we need to separately save the "A" in
				// something like "<div>A<p>B</p>C</div>"
				$this->finishChunk();

				// Empty the XML variable
				$this->xml = '';
			}

			$this->currentChunk ++;

			if ('pb' == $this->r->localName) {
				$this->registerNewPage();
			} elseif ('milestone' == $this->r->localName) {
				$this->column = $this->r->getAttribute('unit');
			} elseif ($sectiontag) {
				$this->currentSection ++;

				if ('text' == $this->r->localName) {
					$this->data['currentVolume'] ++;

					if ($this->settings['textbeforepb']) {
						$this->data['currTextStart'] = $this->page + 1;
					} else {
						$this->data['currTextStart'] = $this->page;
					}
				}

				if (in_array($this->r->localName, $this->setup->structureleveltags)) {
					$this->level ++;
				}

				$this->startSection();

			} elseif ('group' == $this->r->localName) {
				// A group of <text>s, i.e. drop the <text> we saw previously, as it
				// was just the enclosure of some inner <text> inside the <group>.
				$this->data['currentVolume'] = 0;
			}

			if ($chunktag) {
				$this->startChunk();
			}

		} elseif ('titlePart' == $this->r->localName) {
			$this->processTitlePart();
		}

		if (!$this->insidetext) {
			// Not in the main text -- ignore
			return;
		}

		$this->xml .= $this->r->nodeOpenString();

		if (in_array($this->r->localName, $this->setup->blocktags)) {
			$this->xml .= "\n";
		}

		if (!$this->r->isEmptyElement and
			!$nostacktag) {
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

		if ($this->pageObj) {
			// Finish previous page
			$this->pageObj->save();
		}

		$this->page ++;

		$this->pageObj = new Page($this->setup);
		$this->pageObj->number = $this->page;
		$this->pageObj->plaintext = '';
		$this->pageObj->xmlid = $this->r->getAttribute('xml:id');
		$this->pageObj->volume = $this->data['currentVolume'];
		$this->pageObj->n = $this->r->getAttribute('n');
		$this->pageObj->rend = $this->r->getAttribute('rend');
	}

	/**
	 * Saves any remaining page data
	 */
	protected function save() {
		if ($this->pageObj) {
			// Finish previous page
			$this->pageObj->save();
		}
	}


	/**
	 * Method that's called when the input stream reaches a closing tag
	 */
	protected function nodeClose() {

		$nostacktag = in_array($this->r->localName, $this->setup->nostacktags);

		if ($this->insidetext and
			$nostacktag) {
			// Finish data for previous chunk
			$this->finishChunk();

			// Reset variables
			$this->insidetext = false;
			$this->column = null;
			$this->xml = '';

			// Increment the chunk ID to make sure no content is overwritten
			// (this could happen, for instance, at the end of chapters)
			$this->currentChunk ++;
		}

		if (in_array($this->r->localName, $this->setup->structureleveltags)) {
			$this->level --;
		}

		if ($this->currentChunk and
			!$nostacktag) {
			$this->xml .= '</'.$this->r->localName.'>';
			if (in_array($this->r->localName, $this->setup->blocktags)) {
				$this->xml .= "\n";
			}
		}

		if (!$nostacktag) {
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

		$db = $this->setup->database;

		$db->exec(sprintf(
			'INSERT INTO %sstructure (id, volume, title, page, level, element, xmlid) '.
		    'VALUES (%d, %d, %s, %d, %d, %s, %s)',
			$this->setup->prefix,
			$this->currentSection,
			$this->data['currentVolume'],
			$db->quote($title),
			$this->page ? $this->page : 1,
			$this->level,
			$db->quote($this->r->localName),
			$db->quote($this->r->getAttribute('xml:id'))
		));
	}

	/**
	 * Method that's called when a new chunk is encountered. Creates a new
	 * XMLChunk instance and fills it with some data.
	 */
	protected function startChunk() {
		$chunk = new XMLChunk($this->setup);
		$chunk->id = $this->currentChunk;
		$chunk->page = $this->page;
		$chunk->section = $this->currentSection;
		$chunk->column = $this->column;
		$chunk->prestack = join(' ', $this->prestack);
		$this->chunks[$chunk->id] = $chunk;
	}

	/**
	 * Method that's called when a chunk's end is encountered
	 */
	protected function finishChunk() {

		// <pb> and <milestone> have been interpreted, now we can remove them
		$this->xml = preg_replace('#<(?:pb|milestone)\b[^>]*>#', '', $this->xml);

		$plaintext = call_user_func($this->setup->plaintextCallback, $this->xml);

		if ($this->pageObj) {
			$this->pageObj->plaintext .= ' '.trim($plaintext);
		}

		if (empty($this->chunks[$this->currentChunk])) {
			// This method might get called in cases where startChunk()
			// had not been called when it started.
			return;
		}

		$chunk = $this->chunks[$this->currentChunk];
		$chunk->xml = trim($this->xml);
		$chunk->plaintext = $plaintext;
		$chunk->poststack = join(' ', $this->poststack);
		$chunk->save();

		// Dispose of chunk
		unset($this->chunks[$this->currentChunk]);
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
		$db->exec('DELETE FROM '.$prefix.'structure');
		$db->exec('DELETE FROM '.$prefix.'volume');
		Page::flush($this->setup);
		XMLChunk::flush($this->setup);
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
			'INSERT INTO '.$this->setup->prefix.'volume'.' (number, title, pagenumber)
			 VALUES('.$db->quote($this->data['currentVolume']).',
			 '.$db->quote($title).', '.$this->data['currTextStart'].')'
		);

	}
}
