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
     * {@inheritdoc}
     */
	public function createNamedEntity() {
		return new NamedEntity;
	}

    /**
     * {@inheritdoc}
     */
	public function createNamedEntityGateway() {
		return new NamedEntityGateway($this->db, $this, $this->prefix);
	}

    /**
     * {@inheritdoc}
     */
	public function createVolume() {
		return new Volume;
	}

    /**
     * {@inheritdoc}
     */
	public function createVolumeGateway() {
		return new VolumeGateway($this->db, $this, $this->prefix);
	}

    /**
     * {@inheritdoc}
     */
	public function createSection() {
		return new Section;
	}

    /**
     * {@inheritdoc}
     */
	public function createSectionGateway() {
		return new SectionGateway($this->db, $this, $this->prefix);
	}

    /**
     * {@inheritdoc}
     */
	public function createPage() {
		return new Page;
	}

    /**
     * {@inheritdoc}
     */
	public function createPageGateway() {
		return new PageGateway($this->db, $this, $this->prefix);
	}

    /**
     * {@inheritdoc}
     */
	public function createElement() {
		return new Element;
	}

    /**
     * {@inheritdoc}
     */
	public function createElementGateway() {
		return new ElementGateway($this->db, $this, $this->prefix);
	}

    /**
     * {@inheritdoc}
     */
	public function createXMLChunk() {
		return new XMLChunk;
	}

    /**
     * {@inheritdoc}
     */
	public function createXMLChunkGateway() {
		return new XMLChunkGateway($this->db, $this, $this->prefix);
	}

}