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
	 * Does a text's <text> element enclose its first <pb /> element?
	 * Set to false, if the order is <pb /> ... <text>
	 * @var bool
	 */
	public $textBeforePb = true;

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
	 * Flag: are we somewhere inside of a <text>?
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
	 * Variable used to collect the current chunk's contents
	 * @var string
	 */
	protected $xml = null;

	/**
	 * Current <milestone /> @unit and @n, concatenated
	 * @var string
	 */
	protected $milestone = null;

	/**
	 * Text structure level. Only named sections (those with a <head> are
	 * counted, as those without sometimes are only present to comply with TEI. @todo comment?
	 * @var int
	 */
	protected $level = 0;

	/**
	 * The current page object
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

		if ($chunktag or
			$nostacktag) {

			if ($this->currentChunk) {
				// Finish data for previous chunk of XML. We can't simply do that when
				// the tag ends, because we need to separately save the "A" in
				// something like "<div>A<p>B</p>C</div>"
				$this->finishChunk();
				$this->xml = '';
			}

			$this->currentChunk ++;

			if ('pb' == $this->r->localName) {
				$this->newPage();
			} elseif ('milestone' == $this->r->localName) {
				$mlstn = $this->r->getAttribute('unit').'-'.$this->r->getAttribute('n');
				$this->milestone = trim($mlstn, '-');
			} elseif ('group' == $this->r->localName) {
				// A group of <text>s, i.e. drop the <text> we saw previously, as it
				// was just the enclosure of some inner <text> inside the <group>.
				$this->data['currentVolume'] = 0;
				$this->insidetext = false;
			} else {
				$this->currentSection ++;

				if ('text' == $this->r->localName) {

					$this->insidetext = true;
					$this->data['currentVolume'] ++;

					if ($this->textBeforePb) {
						$this->data['currTextStart'] = $this->page + 1;
					} else {
						$this->data['currTextStart'] = $this->page;
					}
				}

				if (in_array($this->r->localName, $this->setup->structureleveltags)) {
					$this->level ++;
				}

				$this->newSection();
			}

			if ($chunktag) {
				$this->startChunk();
			}

		} elseif ('titlePart' == $this->r->localName) {
			$this->processTitlePart();
		}

		$this->xml .= $this->r->nodeOpenString();
		if (in_array($this->r->localName, $this->setup->blocktags)) {
			$this->xml .= "\n";
		}

		if ($this->r->isEmptyElement) {
			return;
		}

		array_push($this->prestack, $this->r->nodeOpenString(true));
		array_unshift($this->poststack, '</'.$this->r->localName.'>');
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
	 * Called when a new page is encountered.
	 */
	protected function newPage() {

		if ($this->pageObj) {
			// Finish previous page
			PageGateway::save($this->setup, $this->pageObj);
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
			PageGateway::save($this->setup, $this->pageObj);
		}
	}

	/**
	 * Method that's called when the input stream reaches a closing tag
	 */
	protected function nodeClose() {

		$textorgroup = in_array($this->r->localName, $this->setup->nostacktags);

		if ($textorgroup) {
			// Finish data for previous chunk
			$this->finishChunk();

			// Reset variables
			$this->insidetext = false;
			$this->milestone = null;
			$this->xml = '';

			// Increment the chunk ID to make sure no content is overwritten
			// (this could happen, for instance, at the end of chapters)
			$this->currentChunk ++;
		}

		if (in_array($this->r->localName, $this->setup->structureleveltags)) {
			$this->level --;
		}

		if ($this->currentChunk and
			!$textorgroup) {
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
	protected function newSection() {

		if ('text'  == $this->r->localName or
		    'front' == $this->r->localName) {
			// <text> must not contain <head>, hence there is no title
			$title = '';
		} elseif ('titlePage' == $this->r->localName) {
			$title = $this->titlePageLabel;
		} else {
			$title = call_user_func($this->setup->titleCallback, $this->r->readOuterXML());
		}

		$section = new Section($this->setup);
		$section->id = $this->currentSection;
		$section->volume = $this->data['currentVolume'];
		$section->title = $title;
		$section->page = $this->page ? $this->page : 1;
		$section->level = $this->level;
		$section->element = $this->r->localName;
		$section->xmlid = $this->r->getAttribute('xml:id');
		SectionGateway::save($this->setup, $section);
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
		$chunk->milestone = $this->milestone;
		$chunk->prestack = join(' ', $this->prestack);
		$chunk->xml = '';
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

		// Dispose of the chunk
		unset($this->chunks[$this->currentChunk]);
	}

	/**
	 * Will be called right before processing starts. Can be used to
	 * check certain conditions (should throw an exception if it fails)
	 * or to perform initialization steps.
	 */
	protected function preProcessAction() {
		SectionGateway::flush($this->setup);
		PageGateway::flush($this->setup);
		VolumeGateway::flush($this->setup);
		XMLChunk::flush($this->setup);
	}

	/**
	 * #todo
	 * @throws RuntimeException
	 */
	protected function processTitlePart() {

			'main' != $this->r->getAttribute('type')) {
		$title = call_user_func(
			$this->setup->plaintextCallback,
			$this->r->readOuterXML()
		);

		// Check for uniqueness
		if (!empty($this->data['volTitles'][$this->data['currentVolume']])) {
			throw new RuntimeException('Multiple <titlePart>...</titlePart>s for volume '.$this->data['currentVolume'].":\n");
		}

		$this->data['volTitles'][$this->data['currentVolume']] = true;

		$volume = new Volume($this->setup);
		$volume->number = $this->data['currentVolume'];
		$volume->title = $title;
		$volume->pagenumber = $this->data['currTextStart'];
		VolumeGateway::save($this->setup, $volume);

	}
}
