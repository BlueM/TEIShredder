<?php

/**
 * #todo
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link http://www.sandrart.net/
 * @version SVN: $Id: Chunker.php 1299 2012-03-21 20:53:00Z cb $
 * @property $database
 * @property $prefix
 * @property $plaintextCallback
 * @property $titleCallback
 */
class TEIShredder_Setup {

	/**
	 * @var bool
	 */
	public $verbose = false;

	/**
	 * Database table prefix.
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Callback function/method/Closure for extracting the
	 * title from a given piece of TEI.
	 * @var string|array|Closure
	 */
	protected $titleCallback;

	/**
	 * Callback function/method/Closure for converting to plaintext
	 * @var string|array|Closure
	 */
	protected $plaintextCallback;

	/**
	 * Database table prefix.
	 * @var PDO
	 */
	protected $database;

	/**
	 * Constructor.
	 * @param PDO $db
	 * @param string $prefix
	 * @param string|array|Closure $ptcallb [optional] Callback for converting
	 *                             to plaintext. If none given, default: 'strip_tags'
	 * @param string|array|Closure $ttlcallb [optional] Callback for extracting the title
	 *                             from part of a TEI document. If none given, defaults
	 *                             to extracting the first <head> and converting it
	 *                             to plaintext using the plaintext conversion callback.
	 * @throws InvalidArgumentException
	 */
	public function __construct(PDO $db, $prefix = '', $ptcallb = null, $ttlcallb = null) {

		$this->database = $db;
		$this->prefix = $prefix;

		if ($ptcallb) {
			if (!is_callable($ptcallb)) {
				throw new InvalidArgumentException('Plaintext conversion callback is invalid');
			}
			$this->plaintextCallback = $ptcallb;
		} else {
			$this->plaintextCallback = 'strip_tags';
		}

		if ($ttlcallb) {
			if (!is_callable($ttlcallb)) {
				throw new InvalidArgumentException('Title extraction callback is invalid');
			}
			$this->titleCallback = $ttlcallb;
		} else {
			$this->titleCallback = function($xml) use ($ptcallb) {
				if (!preg_match('#<head(?:\s+[^>]*)?>(.*?)</head>#s', $xml, $matches)) {
					// No match, return empty string
					return '';
				}
				return call_user_func($ptcallb, $xml);
			};
		}

	}

	/**
	 * #todo
	 * @param $name
	 * @return mixed
	 * @throws UnexpectedValueException
	 */
	public function __get($name) {
		if (in_array($name, array_keys(get_class_vars(__CLASS__)))) {
			return $this->$name;
		}
		throw new UnexpectedValueException("Unexpected member name “".$name."”");
	}
}
