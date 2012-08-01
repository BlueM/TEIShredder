<?php

/**
 * Service locator class whose instances are to be passed to
 * a number of other TEIShredder classes.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @property $prefix
 * @property $titleCallback
 * @property $plaintextCallback
 * @property PDO $database
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
	 *                             to extracting the first <head> child of the section and
	 *                             converting it to plaintext using the plaintext callback.
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
			// Custom title extraction callback
			if (!is_callable($ttlcallb)) {
				throw new InvalidArgumentException('Title extraction callback is invalid');
			}
			$this->titleCallback = $ttlcallb;
		} else {
			// Default title extraction callback
			$ptcallb = $this->plaintextCallback;
			$this->titleCallback = function($xml) use ($ptcallb) {
				$sx = new SimpleXMLElement($xml);
				$head = isset($sx->head[0]) ? $sx->head[0]->asXml() : '';
				return call_user_func($ptcallb, $head);
			};
		}
	}

	/**
	 * Returns one of the class properties' values
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

