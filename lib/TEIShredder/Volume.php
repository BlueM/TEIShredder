<?php

namespace TEIShredder;

use \PDO;

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
	 * Returns all volumes, ordered by their numbers
	 * @param Setup $setup
	 * @return Volume[]
	 */
	public static function fetchVolumes(Setup $setup) {
		$stm = $setup->database->query(
			'SELECT number, title, pagenumber FROM '.$setup->prefix.'volume ORDER BY number'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array($setup));
		$volumes = array();
		foreach ($stm->fetchAll() as $volume) {
			$volumes[] = $volume;
		}
		return $volumes;
	}

	/**
	 * Adds an XML chunk (not expected to perform updates)
	 */
	public function save() {
		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'volume '.
			'(number, title, pagenumber) VALUES (?, ?, ?)'
		);
		$stm->execute(array(
			$this->number,
			$this->title,
			$this->pagenumber,
		));
	}

	/**
	 * Removes all data
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'volume');
	}

}
