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
	 * Returns a sections by its ID
	 * @param Setup $setup
	 * @param int $id
	 * @return Section
	 * @throws \InvalidArgumentException
	 */
	public static function fetchSectionById(Setup $setup, $id) {
		$sth = $setup->database->prepare(
			'SELECT id, volume, title, page, level, element, xmlid '.
			'FROM '.$setup->prefix.'section WHERE id = ?'
		);
		$sth->execute(array($id));
		$sth->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array($setup));
		if (false === $obj = $sth->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

	/**
	 * Returns all sections of a given volume number
	 * @param Setup $setup
	 * @param int $volume Volume number
	 * @return Section[] Indexed array of instances, ordered by the section ID
	 * @throws InvalidArgumentException
	 */
	public static function fetchSectionsByVolume(Setup $setup, $volume) {
		$sth = $setup->database->prepare(
			'SELECT id, volume, title, page, level, element, xmlid '.
			'FROM '.$setup->prefix.'section WHERE level > 0 AND volume = ? ORDER BY id'
		);
		$sth->execute(array($volume));
		$sth->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array($setup));
		return $sth->fetchAll();
	}

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

		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'section '.
			'(id, volume, title, page, level, element, xmlid) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$this->id,
			$this->volume,
			(string)$this->title,
			$this->page,
			$this->level,
			$this->element,
			(string)$this->xmlid,
		));
	}

	/**
	 * Removes all data
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'section');
	}

}
