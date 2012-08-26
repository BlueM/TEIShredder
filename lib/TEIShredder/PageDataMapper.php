<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Data Mapper class for page objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class PageDataMapper implements DataMapperInterface {

	/**
	 * Returns a page object by its page number
	 * @param Setup $setup
	 * @param mixed $number
	 * @return Page
	 * @throws InvalidArgumentException;
	 */
	public static function find(Setup $setup, $number) {
		$sth = $setup->database->prepare(
			'SELECT number, xmlid, volume, plaintext, n, rend '.
			'FROM '.$setup->prefix.'page WHERE number = ?'
		);
		$sth->execute(array($number));
		$sth->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Page', array($setup));
		if (false === $obj = $sth->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @param Setup $setup
	 * @return Page[]
	 */
	public static function findAll(Setup $setup) {
		$stm = $setup->database->query(
			'SELECT number, xmlid, n, rend, volume, plaintext FROM '.$setup->prefix.'page ORDER BY number'
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Page', array($setup));
		return $stm->fetchAll();
	}

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {

		$stm = $setup->database->prepare(
			'INSERT INTO '.$setup->prefix.'page '.
			'(number, xmlid, volume, plaintext, n, rend) '.
			'VALUES (?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$obj->number,
			(string)$obj->xmlid,
			$obj->volume,
			$obj->plaintext,
			(string)$obj->n,
			(string)$obj->rend,
		));

	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'page');
	}

}
