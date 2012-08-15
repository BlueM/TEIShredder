<?php

namespace TEIShredder;

use \PDO;
use \UnexpectedValueException;
use \RuntimeException;

/**
 * #todo
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $number
 * @property string $title
 * @property int $pagenumber
 */
class Volume {

	/**
	 * Volume number.
	 * @var int
	 */
	protected $number;

	/**
	 * Volume's title
	 * @var string
	 */
	protected $title;

	/**
	 * Pagenumber of 1st page in this volume
	 * @var int
	 */
	protected $pagenumber;

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
	 */
	public function save() {

		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'volume (number, title, pagenumber) VALUES (?, ?, ?)'
		);

		$stm->execute(array(
			$this->number,
			$this->title,
			$this->pagenumber,
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
		$setup->database->exec("DELETE FROM ".$setup->prefix.'volume');
	}

}
