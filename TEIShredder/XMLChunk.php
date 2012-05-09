<?php

/**
 * Class for retrieving well-formed parts from the source TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/TEIShredder/
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TEIShredder_XMLChunk {

	/**
	 * Object properties
	 * @var array
	 */
	protected $properties = array(
		'id'=>null,            // Chunk ID
		'col'=>null,           // Info on column that contains this chunk
		'prestack'=>null,      // Open XML tags
		'xml'=>null,           // Chunk's XML source (probably not well-formed)
		'poststack'=>null,     // XML tags to be closed behind
		'section'=>null,       // ID of the chunk's section in the text structure
		'plaintext'=>null,     // ID of the chunk's section in the text structure
	);

	/**
	 * Returns all chunks that are on a given page.
	 * @param TEIShredder_Setup $setup
	 * @param int $page Page number
	 * @return TEIShredder_XMLChunk[]
	 */
	public static function fetchObjectsByPageNumber(TEIShredder_Setup $setup, $page) {

		$objects = array();
		$page = $setup->database->quote($page);
		$res = $setup->database->query(
			"SELECT eid.id, eid.page, eid.col, eid.prestack,
			        eid.poststack, eid.xml, eid.section, eid.plaintext
		     FROM ".$setup->prefix."xmlchunk AS eid
		     WHERE xml != '' AND
		           eid.page = $page
		     ORDER BY eid.id");

		foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$obj = new self;
			$obj->properties = $row;
			$objects[] = $obj;
		}

		return $objects;
	}

	/**
	 * Returns the chunk's ID.
	 * @return int
	 */
	public function getId() {
		return (int)$this->properties['id'];
	}

	/**
	 * Returns the chunk's section's ID
	 * @return int
	 */
	public function getSection() {
		return (int)$this->properties['section'];
	}

	/**
	 * Returns information on the chunk's column
	 * @return string
	 */
	public function getColumn() {
		return $this->properties['col'];
	}

	/**
	 * Returns the chunk's plaintext contents
	 * @return string
	 */
	public function getPlaintext() {
		return $this->properties['plaintext'];
	}

	/**
	 * Returns the chunk's XML source.
	 * @return string
	 */
	public function getXML() {
		return $this->properties['xml'];
	}

	/**
	 * Returns the chunk's content source as well-formed XML, i.e.: with
	 * pre-stack and post-stack tags added, with Unix-style line endings.
	 * @return string
	 */
	public function getWellFormedXML() {
		$xml = $this->properties['prestack'].
		       $this->properties['xml'].
		       $this->properties['poststack'];
		return str_replace("\r", "\n", str_replace("\r\n", "\n", $xml));
	}

}
