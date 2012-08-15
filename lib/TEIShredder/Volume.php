<?php

namespace TEIShredder;

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
	 * Removes all chunks
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'volume');
	}

}
