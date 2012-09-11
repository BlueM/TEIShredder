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
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName() {
		return 'page';
	}

	/**
	 * Returns a page object by its page number
	 * @param Setup $setup
	 * @param mixed $number
	 * @return Page
	 * @throws InvalidArgumentException;
	 */
	public static function find(Setup $setup, $number) {
		$table = $setup->prefix.self::tableName();
		$sth = $setup->database->prepare(
			"SELECT number, xmlid, volume, plaintext, n, rend FROM $table WHERE number = ?"
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
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->query(
			"SELECT number, xmlid, n, rend, volume, plaintext FROM $table ORDER BY number"
		);
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\Page', array($setup));
		return $stm->fetchAll();
	}

}
