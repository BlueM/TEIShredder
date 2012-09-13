<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Gateway for element objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ElementGateway extends AbstractGateway {
	/**
	 * Returns the gateway's database table name
	 * @return string
	 */
	public function tableName() {
		return $this->prefix.'element';
	}

	/**
	 * Returns an element by its unique xml:id
	 * @param mixed $identifier xml:id attribute value
	 * @return Element
	 * @throws InvalidArgumentException
	 */
	public function find($identifier) {
		$table = $this->tableName();
		$stm = $this->db->prepare(
			'SELECT xmlid, element, page, chunk, attrn, attrtargetend, data '.
			"FROM $table WHERE xmlid = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createElement());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

}
