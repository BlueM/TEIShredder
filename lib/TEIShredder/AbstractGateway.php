<?php

namespace TEIShredder;

use \InvalidArgumentException;


/**
 * Abstract base class for all gateway classes
 */
abstract class AbstractGateway {

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, without the configured prefix
	 */
	abstract public function tableName();

	/**
	 * Saves the model
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public function save(Setup $setup, Model $obj) {
		$table = $setup->prefix.$this->tableName();
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
	public function flush(Setup $setup) {
		$table = $setup->prefix.$this->tableName();
		$setup->database->exec("DELETE FROM ".$table);
	}

}
