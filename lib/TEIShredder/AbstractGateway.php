<?php

namespace TEIShredder;

use \InvalidArgumentException;

/**
 * Interface for all Data Mapper classes
 */
interface DataMapperInterface {

	/**
	 * Returns an object by an identifier (which depends on the object domain)
	 * @param Setup $setup
	 * @param mixed $identifier
	 * @return Model
	 * @throws InvalidArgumentException
	 */
	public static function find(Setup $setup, $identifier);

	/**
	 * Returns all objects
	 * @param Setup $setup
	 * @return Model[]
	 */
	public static function findAll(Setup $setup);

	/**
	 * Saves a domain object
	 * @param Setup $setup
	 * @param Model $obj
	 */
	public static function save(Setup $setup, Model $obj);

	/**
	 * Removes all data in the domain
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup);

}
