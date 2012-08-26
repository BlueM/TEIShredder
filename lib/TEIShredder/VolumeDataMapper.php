<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Data Mapper for volume objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class VolumeDataMapper implements DataMapperInterface {

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param Setup $setup
	 * @param mixed $identifier Volume number
	 * @return Volume
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {
		$stm = $setup->database->query(
			'SELECT number, title, pagenumber FROM '.$setup->prefix.'volume ORDER BY number'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Volume', array($setup));
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid volume number');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @param Setup $setup
	 * @return Volume[]
	 */
	public static function findAll(Setup $setup) {
		$stm = $setup->database->query(
			'SELECT number, title, pagenumber FROM '.$setup->prefix.'volume ORDER BY number'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Volume', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {
		$stm = $setup->database->prepare(
			'INSERT INTO '.$setup->prefix.'volume (number, title, pagenumber) VALUES (?, ?, ?)'
		);
		$stm->execute(array(
			$obj->number,
			$obj->title,
			$obj->pagenumber,
		));
	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'volume');
	}

}