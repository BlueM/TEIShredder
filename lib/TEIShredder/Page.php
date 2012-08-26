<?php

namespace TEIShredder;

use \LogicException;
use \InvalidArgumentException;
use \PDO;

/**
 * Model class for physical pages in the underlying TEI document.
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
class Page extends Model {

	/**
	 * Page number. (Numerical unique number, not the "label"
	 * that might have been encoded into @n)
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
	 * @todo Redundancy: Chunks also contain the plaintext.
	 */
	protected $plaintext;

	/**
	 * Saves a page.
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check
		foreach (array('number', 'volume') as $property) {
			if (0 >= intval($this->$property)) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}

		PageDataMapper::save($this->_setup, $this);
	}

}
