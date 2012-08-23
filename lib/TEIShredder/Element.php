<?php

namespace TEIShredder;

use \LogicException;
use \InvalidArgumentException;
use \PDO;

/**
 * Model class for any XML element in the underlying TEI document that is
 * addressable (i.e.: that has an @xml:id attribute).
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $xmlid
 * @property int $element
 * @property int $page
 * @property int $chunk
 * @property string $attrn
 * @property string $attrtargetend
 * @property string $data
 */
class Element extends Model {

	/**
	 * @xml:id value
	 * @var int
	 */
	protected $xmlid;

	/**
	 * Element name (tag)
	 * @var int
	 */
	protected $element;

	/**
	 * Page number
	 * @var string
	 * @todo Redundant: element is assigned to a section, and sections know their page
	 */
	protected $page;

	/**
	 * ID of the chunk which contains this element's start
	 * @var int
	 */
	protected $chunk;

	/**
	 * Value of @n attribute
	 * @var string
	 */
	protected $attrn;

	/**
	 * Value of @targetEnd attribute
	 * @var string
	 */
	protected $attrtargetend;

	/**
	 * Arbitrary other data (depends on element)
	 * @var string
	 */
	protected $data;

	/**
	 * Returns an object by its unique xml:id value
	 * @param Setup $setup
	 * @param string $xmlid
	 * @return Element
	 * @throws InvalidArgumentException
	 */
	public static function fetchElementById(Setup $setup, $xmlid) {
		$sth = $setup->database->prepare(
			'SELECT xmlid, element, page, chunk, attrn, attrtargetend, data '.
			'FROM '.$setup->prefix.'element WHERE xmlid = ?'
		);
		$sth->execute(array($xmlid));
		$sth->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array($setup));
		if (false === $obj = $sth->fetch()) {
			throw new InvalidArgumentException('No such element');
		}
		return $obj;
	}

	/**
	 * Adds an XML chunk (not expected to perform updates)
	 * @throws LogicException
	 */
	public function save() {

		// Basic integrity check
		foreach (array('xmlid', 'element', 'page', 'chunk') as $property) {
			if (is_null($this->$property) or
			    '' === $this->$property) {
				throw new LogicException("Integrity check failed: $property cannot be empty.");
			}
		}

		$stm = $this->_setup->database->prepare(
			'INSERT INTO '.$this->_setup->prefix.'element '.
			'(xmlid, element, page, chunk, attrn, attrtargetend, data) '.
			'VALUES (?, ?, ?, ?, ?, ?, ?)'
		);

		$stm->execute(array(
			$this->xmlid,
			$this->element,
			$this->page,
			$this->chunk,
			$this->attrn,
			$this->attrtargetend,
			$this->data,
		));
	}

	/**
	 * Removes all data
	 * @param Setup $setup
	 */
	public static function flush(Setup $setup) {
		$setup->database->exec("DELETE FROM ".$setup->prefix.'element');
	}

}
