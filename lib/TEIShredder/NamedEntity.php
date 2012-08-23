<?php

namespace TEIShredder;

use \LogicException;

/**
 * Model class for named entities in the underlying TEI document.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $xmlid
 * @property int $page
 * @property string $domain
 * @property string $identifier
 * @property string $contextstart
 * @property string $notation
 * @property string $contextend
 * @property string $container
 * @property int $chunk
 * @property string $notationhash
 */
class NamedEntity extends Model {

	/**
	 * @xml:id value
	 * @var int
	 */
	protected $xmlid;

	/**
	 * Page number
	 * @var string
	 * @todo Redundant: element is assigned to a section, and sections knows their page
	 */
	protected $page;

	/**
	 * Object's domain
	 * @var string
	 */
	protected $domain;

	/**
	 * Value of element's @key attribute in underlying document. Usually some kind
	 * of identifier (database record) or Semantic Web URI.
	 * @var string
	 */
	protected $identifier;

	/**
	 * Context after the notation.
	 * @var int
	 */
	protected $contextend;

	/**
	 * The exact string used in the text to refer to the entity
	 * @var string
	 */
	protected $notation;

	/**
	 * Context before the notation.
	 * @var int
	 */
	protected $contextstart;

	/**
	 * Type of containing element.
	 * @var string
	 * @deprecated
	 * @todo Remove
	 */
	protected $container;

	/**
	 * ID of the chunk which contains this entity's start
	 * @var int
	 */
	protected $chunk;

	/**
	 * Shortened hash of the lowercased notation
	 * @var string
	 */
	protected $notationhash;

	/**
	 * Adds an XML chunk (not expected to perform updates)
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check
		foreach (array('page', 'domain', 'identifier', 'notation') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				$msg = sprintf(
					"Integrity check failed: %s cannot be empty (page %d, context: “%s[…]%s”.",
					$property,
					$this->page,
					$this->contextstart,
					$this->contextend
				);
				throw new LogicException($msg);
			}
		}

		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'entity '.
			'(xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$this->xmlid,
			$this->page,
			$this->domain,
			$this->identifier,
			$this->contextstart,
			$this->notation,
			$this->contextend,
			$this->container,
			$this->chunk,
			substr(md5(mb_convert_case(trim($this->notation), MB_CASE_LOWER)), 0, 10),
		));
	}

	/**
	 * Removes all data
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'entity');
	}

	/**
	 * Magic method for setting protected object properties from outside.
	 * @param string $name Property name
	 * @param mixed $value Value to be assigned
	 */
	public function __set($name, $value) {
		parent::__set($name, $value);

		$length = 100;
		$omission = '…';

		if ('contextstart' == $name) {
		    if (mb_strlen($this->contextstart) >= $length and
			    false !== $pos = strrpos($this->contextstart, ' ', -$length)) {
				// Limit length of the context start
				$this->contextstart = $omission.substr($this->contextstart, $pos);
			}
			return;
		}

		if ('contextend' == $name) {
			if (mb_strlen($this->contextend) >= $length and
			    false !== $pos = strpos($this->contextend, ' ', $length)) {
				// Limit length of the context end
				$this->contextend = substr($this->contextend, 0, $pos).$omission;
			}
		}
	}

}
