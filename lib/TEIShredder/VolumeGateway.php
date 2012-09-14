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
     * {@inheritdoc}
     */
	protected function tableName() {
		return $this->prefix.'volume';
	}

	/**
	 * Returns an object by the volume number
	 * @param mixed $identifier Volume number
	 * @return Volume
	 * @throws InvalidArgumentException
	 */
	public function find($identifier) {
		$table = $this->tableName();
		$stm = $this->db->prepare(
			"SELECT number, title, pagenumber FROM $table WHERE number = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createVolume());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid volume number');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @return Volume[]
	 */
	public function findAll() {
		$table = $this->tableName();
		$stm = $this->db->query(
			"SELECT number, title, pagenumber FROM $table ORDER BY number"
		);
		$page = $this->factory->createVolume();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($page));
		return $stm->fetchAll();
	}


}
