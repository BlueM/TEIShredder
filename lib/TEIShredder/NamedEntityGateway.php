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
class NamedEntityGateway extends AbstractGateway {

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName() {
		return 'entity';
	}

	/**
	 * Returns a named entity by its xml:id attribute value
	 * @param Setup $setup
	 * @param mixed $identifier @xml:id value in the underlying TEI document
	 * @return NamedEntity
	 * @throws InvalidArgumentException
	 * @todo xml:id not suitable (multi-links!)
	 */
	public static function find(Setup $setup, $identifier) {
		$table = $setup->prefix.self::tableName();

		$stm = $setup->database->prepare(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			"FROM $table WHERE xmlid = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_INTO, $setup->factory->createNamedEntity());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid xml:id value');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @param Setup $setup
	 * @return NamedEntity[]
	 */
	public static function findAll(Setup $setup) {
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->query(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			"FROM $table ORDER BY chunk"
		);
		$entity = $setup->factory->createNamedEntity();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($entity));
		return $stm->fetchAll();
	}

}
