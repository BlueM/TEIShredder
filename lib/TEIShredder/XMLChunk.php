<?php

namespace TEIShredder;

use \PDO;
use \LogicException;

/**
 * Class for retrieving well-formed XML fragments from the source TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $id
 * @property string $milestone
 * @property string $page
 * @property string $prestack
 * @property string $xml
 * @property string $poststack
 * @property int $section
 * @property string $plaintext
 */
class XMLChunk extends Model {

	/**
	 * Chunk ID
	 * @var int
	 */
	protected $id;

	/**
	 * Value of last <milestone/>'s @unit and @n, concatenated
	 * @var string
	 */
	protected $milestone;

	/**
	 * Number of the page this chunk is on
	 * @var int
	 */
	protected $page;

	/**
	 * String of open XML tags at the point in the source document
	 * where this chunk of XML starts.
	 * @var string
	 */
	protected $prestack;

	/**
	 * Chunk's XML source (probably not well-formed)
	 * @var string
	 */
	protected $xml;

	/**
	 * XML tags to be closed after this chunk of XML.
	 * @var
	 */
	protected $poststack;

	/**
	 * ID of the chunk's section in the text structure
	 * @var int
	 */
	protected $section;

	/**
	 * Chunk's plaintext representation
	 * @var string
	 */
	protected $plaintext;

	/**
	 * Returns an associative array of property=>value pairs to be
	 * processed by a persistence layer.
	 * @return array
	 * @throws LogicException
	 */
	public function persistableData() {
		// Basic integrity check
		foreach (array('page', 'section') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}
		return $this->toArray();
	}

	/**
	 * Returns the chunk's content source as well-formed XML, i.e.: with
	 * pre-stack and post-stack tags added, with Unix-style line endings.
	 * @return string
	 */
	public function getWellFormedXML() {
		return str_replace(array("\r\n", "\n"), "\n", $this->prestack.$this->xml.$this->poststack);
	}

}
