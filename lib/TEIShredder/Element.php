<?php

namespace TEIShredder;

use \LogicException;
use \InvalidArgumentException;
use \PDO;

/**
 * Model class for any XML element in the underlying TEI document that is
 * addressable (i.e.: that has an @xml:id attribute).
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $xmlid
 * @property int $element
 * @property int $page
 * @property int $chunk
 */
class Element extends Model {

	/**
	 * @xml:id value
	 * @var int
	 */
	protected $xmlid;

	/**
	 * Element name (tag)
	 * @var int
	 */
	protected $element;

	/**
	 * Page number
	 * @var string
	 * @todo Redundant: element is assigned to a section, and sections know their page
	 */
	protected $page;

	/**
	 * ID of the chunk which contains this element's start
	 * @var int
	 */
	protected $chunk;

	/**
	 * Returns an associative array of property=>value pairs to be
	 * processed by a persistence layer.
	 * @return array
	 * @throws LogicException
	 */
	public function persistableData() {
		// Basic integrity check
		foreach (array('xmlid', 'element', 'page', 'chunk') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}
		return $this->toArray();
	}

}
