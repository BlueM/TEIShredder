<?php

/**
 * Class for retrieving the edition's prepared text
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link http://www.sandrart.net/
 * @version SVN: $Id: TextChunk.php 1289 2012-03-20 15:17:53Z cb $
 */
class TEIShredder_Chunk {

	/**
	 * Prefix for the database tables' names
	 * @var string
	 */
	public static $prefix = '';

	/**
	 * Object properties
	 * @var array
	 */
	protected $properties = array(
		'id'=>null,            // Chunk ID
		'volume'=>null,        // Volume number
		'page'=>null,          // Page number
		'col'=>null,           // Info on column that contains this chunk
		'pagenotation'=>null,  // Page notation / unique page "title"
		'section'=>null,       // ID of the chunk's section in the text structure
		'plaintext'=>null,     // ID of the chunk's section in the text structure
		'xml'=>null,           // Chunk's XML source (probably not well-formed)
		'prestack'=>null,      // Open XML tags
		'poststack'=>null,     // XML tags to be closed behind
	);

	/**
	 * Returns all chunks that are on a given page.
	 * @param PDO $db
	 * @param int Page number.
	 * @return TEIShredder_Chunk[]
	 */
	public static function fetchObjectsByPageNumber(PDO $db, $page) {

		$objects = array();
		$page = $db->quote($page);
		$res = $db->query("SELECT eid.id, eid.volume, eid.page, eid.col,
		                          eid.prestack, eid.poststack, eid.xml,
		                          pg.pagenotation, eid.section, eid.plaintext
		                   FROM ".static::$prefix."xmlchunk AS eid, ".static::$prefix."page AS pg
		                   WHERE xml != '' AND
		                         eid.page = $page AND
		                         pg.page = eid.page
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
		return $this->properties['id'];
	}

	/**
	 * Returns the volume number.
	 * @return int
	 */
	public function getVolume() {
		return $this->properties['volume'];
	}

	/**
	 * Returns the number of the page this chunk is on
	 * @return int
	 */
	public function getPage() {
		return $this->properties['page'];
	}

	/**
	 * Returns the chunk's section's ID
	 * @return int
	 */
	public function getSection() {
		return $this->properties['section'];
	}

	/**
	 * Returns information on the chunk's column
	 * @return string
	 */
	public function getColumn() {
		return $this->properties['col'];
	}

	/**
	 * Alias for getColumn()
	 * @return string
	 */
	public function getCol() {
		return $this->getColumn();
	}

	/**
	 * Returns the page's notation
	 * @return string
	 */
	public function getPageNotation() {
		return $this->properties['pagenotation'];
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
	public function getXml() {
		return $this->properties['xml'];
	}

	/**
	 * Returns the chunk's content source as well-formed XML, i.e.: with
	 * pre-stack and post-stack tags added, with Unix-style line endings.
	 * @return string
	 */
	public function getWellFormedXml() {
		$xml = $this->properties['prestack'].
		       $this->properties['xml'].
		       $this->properties['poststack'];
		$xml = str_replace("\r", "\n", str_replace("\r\n", "\n", $xml));
		return $xml;
	}

	/**
	 * Returns properties as name=>value array.
	 * @return array Ass. array of instance variable=>value pairs.
	 */
	public function toArray() {
		$array = array();
		foreach (array_keys($this->properties) as $property) {
			$array[$property] = $this->{"get$property"}();
		}
		return $array;
	}

	/**
	 * Returns an HTML representation of this chunk, primarily for
	 * debugging and development purposes.
	 * @return string Short HTML representation.
	 */
	public function __toString() {
		$xml = trim($this->properties['xml']);
		if ($xml) {
			$xml = preg_replace('#[\n\r]+#', ' ', htmlspecialchars($xml));
			$xml = "\n      ".substr($xml, 0, 80);
			$text = trim(strip_tags($xml));
			if ($text) {
				$text = preg_replace('#[\n\r]+#', ' ', htmlspecialchars($text));
				$text = "\n      &ldquo;".substr($text, 0, 70)."&rdquo;";
			}
		} else {
			$text = '';
		}
		return sprintf(
			"<pre> %s #%d, p. %d [%s]%s</pre>",
			__CLASS__,
			$this->properties['id'],
			$this->properties['page'],
			$this->properties['col'],
			$text
		);
	}

}
