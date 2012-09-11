<?php

namespace TEIShredder;

use \LogicException;

/**
 * Model class for volumes
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $number
 * @property string $title
 * @property int $pagenumber
 */
class Volume extends Model {

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
	 * Returns an associative array of property=>value pairs to be
	 * processed by a persistence layer.
	 * @return array
	 * @throws LogicException
	 */
	public function persistableData() {
		// Basic integrity check
		foreach (array('number', 'pagenumber') as $property) {
			if (0 >= intval($this->$property)) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}
		return $this->toArray();
	}

}
