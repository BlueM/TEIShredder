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
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName() {
		return 'element';
	}

	/**
	 * Returns an element by its unique xml:id
	 * @param Setup $setup
	 * @param mixed $identifier xml:id attribute value
	 * @return Element
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->prepare(
			'SELECT xmlid, element, page, chunk, attrn, attrtargetend, data '.
			"FROM $table WHERE xmlid = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Element');
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

}
