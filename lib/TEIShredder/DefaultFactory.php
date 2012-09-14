<?php

/**
 * Copyright (c) 2012, Carsten Blüm <carsten@bluem.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Default factory for creating objects.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
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