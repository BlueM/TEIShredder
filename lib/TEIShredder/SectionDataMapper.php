<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Data Mapper class for page objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class SectionDataMapper implements DataMapperInterface {

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param Setup $setup
	 * @param mixed $identifier Section ID
	 * @return Model
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {
		$stm = $setup->database->query(
			'SELECT id, volume, title, page, level, element, xmlid '.
			'FROM '.$setup->prefix.'section WHERE id = ?'
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section', array($setup));
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid section ID');
		}
		return $obj;
	}

	/**
	 * Returns all sections, ordered by their ID
	 * @param Setup $setup
	 * @return Section[]
	 */
	public static function findAll(Setup $setup) {
		$stm = $setup->database->query(
			'SELECT id, volume, title, page, level, element, xmlid '.
			'FROM '.$setup->prefix.'section WHERE level > 0 ORDER BY id'
		);
		$stm->execute();
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Returns all sections in a given volume, ordered by their ID
	 * @param Setup $setup
	 * @param int $volume Volume number
	 * @return Section[]
	 */
	public static function findAllInVolume(Setup $setup, $volume) {
		$stm = $setup->database->query(
			'SELECT id, volume, title, page, level, element, xmlid '.
			'FROM '.$setup->prefix.'section WHERE level > 0 AND volume = ? ORDER BY id'
		);
		$stm->execute(array($volume));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {
		$stm = $setup->database->prepare(
			'INSERT INTO '.$setup->prefix.'section '.
			'(id, volume, title, page, level, element, xmlid) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$obj->id,
			$obj->volume,
			(string)$obj->title,
			$obj->page,
			$obj->level,
			$obj->element,
			(string)$obj->xmlid,
		));

	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'section');
	}

}