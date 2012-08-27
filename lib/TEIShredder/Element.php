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
 * @property string $attrn
 * @property string $attrtargetend
 * @property string $data
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
	 * Value of @n attribute
	 * @var string
	 */
	protected $attrn;

	/**
	 * Value of @targetEnd attribute
	 * @var string
	 */
	protected $attrtargetend;

	/**
	 * Arbitrary other data (depends on element)
	 * @var string
	 */
	protected $data;

	/**
	 * Adds an XML chunk (not expected to perform updates)
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check
		foreach (array('xmlid', 'element', 'page', 'chunk') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}

		ElementDataMapper::save($this->_setup, $this);
	}

}
