<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Gateway for page objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class SectionGateway extends AbstractGateway {

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	public function tableName() {
		return 'section';
	}

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param Setup $setup
	 * @param mixed $identifier Section ID
	 * @return Model
	 * @throws InvalidArgumentException
	 */
	public function find(Setup $setup, $identifier) {
		$table = $setup->prefix.$this->tableName();
		$stm = $setup->database->query(
			"SELECT id, volume, title, page, level, element, xmlid FROM $table WHERE id = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section');
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
	public function findAll(Setup $setup) {
		$table = $setup->prefix.$this->tableName();
		$stm = $setup->database->query(
			'SELECT id, volume, title, page, level, element, xmlid '.
			"FROM $table WHERE level > 0 ORDER BY id"
		);
		$stm->execute();
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section');
		return $stm->fetchAll();
	}

	/**
	 * Returns all sections in a given volume, ordered by their ID
	 * @param Setup $setup
	 * @param int $volume Volume number
	 * @return Section[]
	 */
	public function findAllInVolume(Setup $setup, $volume) {
		$table = $setup->prefix.$this->tableName();
		$stm = $setup->database->prepare(
			'SELECT id, volume, title, page, level, element, xmlid '.
			"FROM $table WHERE level > 0 AND volume = ? ORDER BY id"
		);
		$stm->execute(array($volume));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Section');
		return $stm->fetchAll();
	}

}