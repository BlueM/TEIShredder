<?php

namespace TEIShredder;

use \PDO;
use \UnexpectedValueException;
use \RuntimeException;
use \LogicException;

/**
 * Model class for sections in the underlying TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $id
 * @property int $volume
 * @property string $title
 * @property int $page
 * @property int $level
 * @property string $element
 * @property string $xmlid
 */
class Section {

	/**
	 * Section ID
	 * @var int
	 */
	protected $id;

	/**
	 * Volume number
	 * @var int
	 * @todo Redundant: a section starts on a page, and the page knows the volume
	 */
	protected $volume;

	/**
	 * Section's title
	 * @var string
	 */
	protected $title;

	/**
	 * Number of the page on which this sections starts
	 * @var int
	 */
	protected $page;

	/**
	 * Section's level (1-based) in the section hierarchy
	 * @var int
	 */
	protected $level;

	/**
	 * Section's element (tag) name
	 * @var int
	 */
	protected $element;

	/**
	 * Section's opening tag's @xml:id attribute value
	 * @var string
	 */
	protected $xmlid;

	/**
	 * Instance of the Setup class.
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
	 * Adds an XML chunk (not expected to perform updates)
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check: make sure that properties
		// that can never be empty are not empty.
		foreach (array('id', 'volume', 'page', 'level', 'element') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}

		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'section '.
			'(id, volume, title, page, level, element, xmlid) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$this->id,
			$this->volume,
			(string)$this->title,
			$this->page,
			$this->level,
			$this->element,
			(string)$this->xmlid,
		));
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
	 * Removes all chunks
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'section');
	}

}
