<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Data Mapper for element objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ElementDataMapper implements DataMapperInterface {

	/**
	 * Returns an element by its unique xml:id
	 * @param Setup $setup
	 * @param mixed $identifier xml:id attribute value
	 * @return Element
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {
		$stm = $setup->database->prepare(
			'SELECT xmlid, element, page, chunk, attrn, attrtargetend, data '.
			'FROM '.$setup->prefix.'element WHERE xmlid = ?'
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Element', array($setup));
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

	/**
	 * Returns all elements, ordered by the chunk ID
	 * @param Setup $setup
	 * @return Element[]
	 */
	public static function findAll(Setup $setup) {
		$stm = $setup->database->query(
			'SELECT xmlid, element, page, chunk, attrn, attrtargetend, data '.
			'FROM '.$setup->prefix.'element ORDER BY chunk'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Element', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {
		$stm = $setup->database->prepare(
			'INSERT INTO '.$setup->prefix.'element '.
			'(xmlid, element, page, chunk, attrn, attrtargetend, data) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$obj->xmlid,
			$obj->element,
			$obj->page,
			$obj->chunk,
			$obj->attrn,
			$obj->attrtargetend,
			$obj->data,
		));
	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'element');
	}

}
