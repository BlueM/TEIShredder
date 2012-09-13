<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Default factory for creating objects.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DefaultFactory implements FactoryInterface {

	/**
	 * @var PDO $db
	 */
	protected $db;

	/**
	 * @var string $prefix
	 */
	protected $prefix;

	/**
	 * Constructor.
	 * @param PDO $db
	 * @param string $prefix
	 */
	public function __construct(PDO $db, $prefix = '') {
		$this->db = $db;
		$this->prefix = $prefix;
	}

	/**
	 * @return NamedEntity
	 */
	public function createNamedEntity() {
		return new NamedEntity;
	}

	/**
	 * @return NamedEntityGateway
	 */
	public function createNamedEntityGateway() {
		return new NamedEntityGateway($this->db, $this, $this->prefix);
	}

	/**
	 * @return Volume
	 */
	public function createVolume() {
		return new Volume;
	}

	/**
	 * @return VolumeGateway
	 */
	public function createVolumeGateway() {
		return new VolumeGateway($this->db, $this, $this->prefix);
	}

	/**
	 * @return Section
	 */
	public function createSection() {
		return new Section;
	}

	/**
	 * @return SectionGateway
	 */
	public function createSectionGateway() {
		return new SectionGateway($this->db, $this, $this->prefix);
	}

	/**
	 * @return Page
	 */
	public function createPage() {
		return new Page;
	}

	/**
	 * @return PageGateway
	 */
	public function createPageGateway() {
		return new PageGateway($this->db, $this, $this->prefix);
	}

	/**
	 * @return Element
	 */
	public function createElement() {
		return new Element;
	}

	/**
	 * @return ElementGateway
	 */
	public function createElementGateway() {
		return new ElementGateway($this->db, $this, $this->prefix);
	}

	/**
	 * @return XMLChunk
	 */
	public function createXMLChunk() {
		return new XMLChunk;
	}

	/**
	 * @return XMLChunkGateway
	 */
	public function createXMLChunkGateway() {
		return new XMLChunkGateway($this->db, $this, $this->prefix);
	}

}