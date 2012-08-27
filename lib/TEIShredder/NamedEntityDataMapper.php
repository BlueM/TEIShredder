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
class NamedEntityDataMapper implements DataMapperInterface {

	/**
	 * Returns a named entity by its xml:id attribute value
	 * @param Setup $setup
	 * @param mixed $identifier @xml:id value in the underlying TEI document
	 * @return NamedEntity
	 * @throws InvalidArgumentException
	 * @todo xml:id not suitable (multi-links!)
	 */
	public static function find(Setup $setup, $identifier) {
		$stm = $setup->database->prepare(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			'FROM '.$setup->prefix.'entity WHERE xmlid = ?'
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\NamedEntity', array($setup));
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
		$stm = $setup->database->query(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			'FROM '.$setup->prefix.'entity ORDER BY chunk'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\NamedEntity', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {

		$stm = $setup->database->prepare(
			'INSERT INTO '.$setup->prefix.'entity '.
			'(xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$obj->xmlid,
			$obj->page,
			$obj->domain,
			$obj->identifier,
			$obj->contextstart,
			$obj->notation,
			$obj->contextend,
			$obj->container,
			$obj->chunk,
			$obj->notationhash,
		));

	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'entity');
	}

}
