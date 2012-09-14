<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Gateway class for page objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class PageGateway extends AbstractGateway {

    /**
     * {@inheritdoc}
     */
	protected function tableName() {
		return $this->prefix.'page';
	}

	/**
	 * Returns a page object by its page number
	 * @param mixed $number
	 * @return Page
	 * @throws InvalidArgumentException;
	 */
	public function find($number) {
		$table = $this->tableName();
		$stm = $this->db->prepare(
			"SELECT number, xmlid, volume, plaintext, n, rend FROM $table WHERE number = ?"
		);
		$stm->execute(array($number));
		$stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createPage());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @return Page[]
	 */
	public function findAll() {
		$table = $this->tableName();
		$stm = $this->db->query(
			"SELECT number, xmlid, n, rend, volume, plaintext FROM $table ORDER BY number"
		);
		$page = $this->factory->createPage();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($page));
		return $stm->fetchAll();
	}

}
