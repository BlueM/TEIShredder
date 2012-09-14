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
     * {@inheritdoc}
     */
	protected function tableName() {
		return $this->prefix.'section';
	}

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param mixed $identifier Section ID
	 * @return Model
	 * @throws InvalidArgumentException
	 */
	public function find($identifier) {
		$table = $this->tableName();
		$stm = $this->db->query(
			"SELECT id, volume, title, page, level, element, xmlid FROM $table WHERE id = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createSection());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid section ID');
		}
		return $obj;
	}

	/**
	 * Returns all sections, ordered by their ID
	 * @return Section[]
	 */
	public function findAll() {
		$table = $this->tableName();
		$stm = $this->db->query(
			'SELECT id, volume, title, page, level, element, xmlid '.
			"FROM $table WHERE level > 0 ORDER BY id"
		);
		$stm->execute();
		$section = $this->factory->createSection();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($section));
		return $stm->fetchAll();
	}

	/**
	 * Returns all sections in a given volume, ordered by their ID
	 * @param int $volume Volume number
	 * @return Section[]
	 */
	public function findAllInVolume($volume) {
		$table = $this->tableName();
		$stm = $this->db->prepare(
			'SELECT id, volume, title, page, level, element, xmlid '.
			"FROM $table WHERE level > 0 AND volume = ? ORDER BY id"
		);
		$section = $this->factory->createSection();
		$stm->execute(array($volume));
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($section));
		return $stm->fetchAll();
	}

}