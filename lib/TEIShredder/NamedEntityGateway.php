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
 * Gateway for volume objects
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class NamedEntityGateway extends AbstractGateway {

    /**
     * {@inheritdoc}
     */
	protected function tableName() {
		return $this->prefix.'entity';
	}

	/**
	 * Returns a named entity by its xml:id attribute value
	 * @param mixed $identifier @xml:id value in the underlying TEI document
	 * @return NamedEntity
	 * @throws InvalidArgumentException
	 * @todo xml:id not suitable (multi-links!)
	 */
	public function find($identifier) {
		$table = $this->tableName();

		$stm = $this->db->prepare(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			"FROM $table WHERE xmlid = ?"
		);
		$stm->execute(array($identifier));
		$stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createNamedEntity());
		if (false === $obj = $stm->fetch()) {
			throw new InvalidArgumentException('Invalid xml:id value');
		}
		return $obj;
	}

	/**
	 * Returns all objects
	 * @return NamedEntity[]
	 */
	public function findAll() {
		$table = $this->tableName();
		$stm = $this->db->query(
			'SELECT xmlid, page, domain, identifier, contextstart, notation, contextend, container, chunk, notationhash '.
			"FROM $table ORDER BY chunk"
		);
		$entity = $this->factory->createNamedEntity();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($entity));
		return $stm->fetchAll();
	}

}
