<?php

namespace TEIShredder;

use \PDO;
use \UnexpectedValueException;
use \RuntimeException;

/**
 * Class for retrieving well-formed XML fragments from the source TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $id
 * @property string $column
 * @property string $page
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
	 * ID of the chunk's section in the text structure
	 * @var string
	 */
	protected $plaintext;

	/**
	 * Instance of the Setup class. Not settable by __set().
	 * @var Setup
	 */
	protected $_setup;

	/**
	 * Constructor.
	 * @param Setup $setup
	 */
	function __construct(Setup $setup) {
		$this->_setup = $setup;
	}

	/**
	 * Removes all chunks
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'xmlchunk');
	}

	/**
	 * Adds an XML chunk (not expected to perform updates)
	 */
	public function save() {
		$db = $this->_setup->database;

		$stm = $db->prepare(
			'INSERT INTO '.$this->_setup->prefix.'xmlchunk '.
			'(id, page, section, column, prestack, xml, poststack, plaintext) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
		);

		// Don't check for return value, as it should
		// throw an exception if it fails.
		$stm->execute(array(
			$this->id,
			$this->page,
			$this->section,
			$this->column,
			$this->prestack,
			$this->xml,
			$this->poststack,
			$this->plaintext,
		));
	}

	/**
	 * Returns all chunks that are on a given page.
	 * @param Setup $setup
	 * @param int $page Page number
	 * @return XMLChunk[]
	 */
	public static function fetchObjectsByPageNumber(Setup $setup, $page) {
		$stm = $setup->database->prepare(
			"SELECT eid.id, eid.column, eid.prestack, eid.xml, eid.poststack, eid.section, eid.plaintext
		     FROM ".$setup->prefix."xmlchunk AS eid
		     WHERE xml != '' AND eid.page = ?
		     ORDER BY eid.id"
		);
		$stm->execute(array($page));
		$stm->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array($setup));
		return $stm->fetchAll();
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
		throw new UnexpectedValueException("Invalid property name “".$name."”");
	}

	/**
	 * Magic method for setting protected object properties from outside.
	 * @param string $name Property name
	 * @param mixed $value Value to be assigned
	 * @throws UnexpectedValueException
	 */
	public function __set($name, $value) {
		$properties = array_keys(get_class_vars(__CLASS__));
		if (!in_array($name, $properties)) {
			throw new UnexpectedValueException("Invalid property name “".$name."”.");
		}
		if ('_' === substr($name, 0, 1)) {
			throw new UnexpectedValueException("Property “".$name."” can not be set.");
		}
		$this->$name = $value;
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
