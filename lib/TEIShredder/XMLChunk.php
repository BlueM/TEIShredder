<?php

namespace TEIShredder;

use \PDO;
use \UnexpectedValueException;

/**
 * Class for retrieving well-formed XML fragments from the source TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @property int $id
 * @property string $column
 * @property string $prestack
 * @property string $xml
 * @property string $poststack
 * @property int $section
 * @property string $plaintext
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
	protected $column;

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
			"SELECT ?, eid.id, eid.column, eid.prestack, eid.xml, eid.poststack, eid.section, eid.plaintext
		     FROM ".$setup->prefix."xmlchunk AS eid
		     WHERE xml != '' AND
		           eid.page = ?
		     ORDER BY eid.id"
		);
		$stm->execute(array(__CLASS__, $page));
		return $stm->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_CLASSTYPE);
	}

	/**
	 * Returns one of the class properties' values
	 * @param $name
	 * @return mixed
	 * @throws UnexpectedValueException
	 */
	public function __get($name) {
		if (in_array($name, array_keys(get_class_vars(__CLASS__)))) {
			return $this->$name;
		}
		throw new UnexpectedValueException("Unexpected member name “".$name."”");
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
