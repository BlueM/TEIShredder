<?php

namespace TEIShredder;

use \PDO;

/**
 * Class for retrieving well-formed parts from the source TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class XMLChunk {

	/**
	 * Chunk ID
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Info on column that contains this chunk
	 * @var string
	 */
	protected $col;

	/**
	 * Open XML tags at the point in the source document where this
	 * chunk of XML starts.
	 * @var
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
	 * ID of the chunk's section in the text structure
	 * @var string
	 */
	protected $plaintext;

	/**
	 * Returns all chunks that are on a given page.
	 * @param Setup $setup
	 * @param int $page Page number
	 * @return XMLChunk[]
	 */
	public static function fetchObjectsByPageNumber(Setup $setup, $page) {
		$stm = $setup->database->prepare(
			"SELECT ?, eid.id, eid.col, eid.prestack, eid.xml, eid.poststack, eid.section, eid.plaintext
		     FROM ".$setup->prefix."xmlchunk AS eid
		     WHERE xml != '' AND
		           eid.page = ?
		     ORDER BY eid.id"
		);
		$stm->execute(array(__CLASS__, $page));
		return $stm->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_CLASSTYPE);
	}

	/**
	 * Returns the chunk's ID.
	 * @return int
	 */
	public function getId() {
		return (int)$this->id;
	}

	/**
	 * Returns the chunk's section's ID
	 * @return int
	 */
	public function getSection() {
		return (int)$this->section;
	}

	/**
	 * Returns information on the chunk's column
	 * @return string
	 */
	public function getColumn() {
		return $this->col;
	}

	/**
	 * Returns the chunk's plaintext contents
	 * @return string
	 */
	public function getPlaintext() {
		return $this->plaintext;
	}

	/**
	 * Returns the chunk's XML source.
	 * @return string
	 */
	public function getXML() {
		return $this->xml;
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
