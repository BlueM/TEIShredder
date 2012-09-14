<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Abstract base class for all gateway classes
 */
abstract class AbstractGateway {

	/**
	 * @var PDO $db
	 */
	protected $db;

	/**
	 * @var string $prefix
	 */
	protected $prefix;

	/**
	 * @var FactoryInterface $db
	 */
	protected $factory;

	/**
	 * Returns the gateway's database table name
	 * @return string Table name, including the configured prefix
	 */
	abstract protected function tableName();

	/**
	 * Constructor.
	 * @param PDO $db
	 * @param FactoryInterface $factory
	 * @param string $prefix
	 */
	public function __construct(PDO $db, FactoryInterface $factory, $prefix = '') {
		$this->db = $db;
		$this->factory = $factory;
		$this->prefix = $prefix;
	}

	/**
	 * Saves the model
	 * @param Model $obj
	 */
	public function save(Model $obj) {
		$table = $this->tableName();
		$data = $obj->persistableData();
		$columns = join(', ', array_keys($data));
		$values = array_values($data);
		$stm =$this->db->prepare(
			"INSERT INTO $table ($columns) VALUES (".trim(str_repeat('?, ', count($values)), ', ').')'
		);
		$stm->execute($values);
	}

	/**
	 * Removes all data in the domain
	 */
	public function flush() {
		$table = $this->tableName();
		$this->db->exec("DELETE FROM ".$table);
	}

}
