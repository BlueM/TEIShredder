<?php

namespace TEIShredder;

use \UnexpectedValueException;
use \LogicException;

/**
 * Simple base class for TEIShredder model classes.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Model {

	/**
	 * Instance of the Setup class.
	 * @var Setup
	 */
	protected $_setup;

	/**
	 * Returns data to be passed to a persistence layer.
	 *
	 * Concrete subclasses can use this method to check the consistency
	 * of the objects state and throw an exception, if validation failed.
	 * @return array Associative array of property=>value pairs
	 */
	abstract public function persistableData();

	/**
	 * Constructor.
	 * @param Setup $setup
	 * @todo Do we still need the Setup instance once the persistence is not done
	 *       by the model classes anymore?
	 */
	public function __construct(Setup $setup) {
		$this->_setup = $setup;
	}

	/**
	 * Returns one of the class properties' values
	 * @param $name
	 * @return mixed
	 * @throws UnexpectedValueException
	 */
	public function __get($name) {
		if (in_array($name, array_keys($this->toArray()))) {
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
		if ('_' === substr($name, 0, 1)) {
			throw new UnexpectedValueException("Property “".$name."” can not be set.");
		}
		if (!in_array($name, array_keys($this->toArray()))) {
			throw new UnexpectedValueException("Invalid property name “".$name."”.");
		}
		$this->$name = $value;
	}

	/**
	 * Returns a string representation of the object
	 * @return string
	 */
	public function __toString() {
		$properties = array();
		foreach ($this->toArray() as $property=>$value) {
			$properties[] = $property.': '.$value;
		}
		$properties = join(', ', $properties);
		return get_class($this).($properties ? " [$properties]" : '');
	}

	/**
	 * Returns an array representation of the object.
	 * @return array
	 */
	protected function toArray() {
		$array = array();
		foreach ($this as $property=>$value) {
			if (strncmp('_', $property, 1)) {
				$array[$property] = $value;
			}
		}
		return $array;
	}

}
