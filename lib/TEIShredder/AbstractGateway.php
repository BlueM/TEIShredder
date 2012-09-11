<?php

namespace TEIShredder;

use \InvalidArgumentException;

/**
 * #todo
 */
interface AbstractGatewayInterface {

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName();

}

/**
 * Abstract base class for all gateway classes
 */
abstract class AbstractGateway implements AbstractGatewayInterface {

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param Setup $setup
	 * @param mixed $identifier
	 * @return Model
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier) {

	}

	/**
	 * Returns all objects
	 * @param Setup $setup
	 * @return Model[]
	 */
	public static function findAll(Setup $setup) {

	}

	/**
	 * Saves the model
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj) {
		$table = $setup->prefix.static::tableName();
		$data = $obj->persistableData();
		$columns = join(', ', array_keys($data));
		$values = array_values($data);
		$stm = $setup->database->prepare(
			"INSERT INTO $table ($columns) VALUES (".trim(str_repeat('?, ', count($values)), ', ').')'
		);
		$stm->execute($values);
	}

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$table = $setup->prefix.static::tableName();
		$setup->database->exec("DELETE FROM ".$table);
	}

}
