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
 * @property string $xmlid
 * @property string $n
 * @property string $rend
 * @property int $volume
 * @property string $plaintext
 */
class Page {

	/**
	 * Page number. (Numerical unique number, not the arabic or Roman
	 * number that might have been encoded into @n.)
	 * @var int
	 */
	protected $number;

	/**
	 * Value of <pb />'s @xml:id attribute value
	 * @var string
	 */
	protected $xmlid;

	/**
	 * Value of <pb />'s @n attribute value
	 * @var string
	 */
	protected $n;

	/**
	 * Value of <pb />'s @rend attribute value
	 * @var int
	 */
	protected $rend;

	/**
	 * Volume number
	 * @var int
	 */
	protected $volume;

	/**
	 * Plaintext
	 * @var int
	 * @todo Chunks also contain the plaintext. Probably a redundancy.
	 */
	protected $plaintext;

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
		$setup->database->exec("DELETE FROM ".$setup->prefix.'page');
	}

}