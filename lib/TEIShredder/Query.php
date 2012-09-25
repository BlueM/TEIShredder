<?php

namespace TEIShredder;

use \PDO;
use \InvalidArgumentException;

/**
 * #todo
 * @package #todo
 */
class Query {

	protected $_queries = array();

	protected $_db;

	/**
	 * Constructor.
	 * @param PDO $db
	 */
	public function __construct(PDO $db) {
		$this->_db = $db;
	}

	/**
	 * @param $property
	 * @param $operator
	 * @param $value
	 * @throws InvalidArgumentException
	 */
	public function addQuery($property, $operator, $value) {
		$operators = array('<', '>', '=', '!=');
		if (!in_array($operator, $operators)) {
			throw new InvalidArgumentException('Invalid operator');
		}
		$where = $operator.' '.$this->_db->quote($value);
		$this->queries[] = array($property, $where);
	}

}