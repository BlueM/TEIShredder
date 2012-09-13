<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Gateway for volume objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class VolumeGateway extends AbstractGateway {

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName() {
		return 'volume';
	}

	/**
	 * Returns an object by the volume number
	 * @param Setup $setup
	 * @param mixed $identifier Volume number
	 * @return Volume
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->prepare(
			"SELECT number, title, pagenumber FROM $table WHERE number = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Volume');
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
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->query(
			"SELECT number, title, pagenumber FROM $table ORDER BY number"
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Volume');
		return $stm->fetchAll();
	}


}
