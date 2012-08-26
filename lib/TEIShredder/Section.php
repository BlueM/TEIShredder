<?php

namespace TEIShredder;

use \LogicException;
use \InvalidArgumentException;
use \PDO;

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
class Section extends Model {

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
	 * Adds an XML chunk (not expected to perform updates)
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check
		foreach (array('id', 'volume', 'page', 'level', 'element') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}

		SectionDataMapper::save($this->_setup, $this);
	}

}
