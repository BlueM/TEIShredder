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
	 * @param array $criteria [optional] One or more pairs of instance variable
	 *                        name=>value pairs, which will be AND-ed. If empty,
	 *                        all Elements will be returned.
	 * @return NamedEntity[]
	 * @throws InvalidArgumentException
	 */
	public function find(array $criteria = array()) {
		$entity = $this->factory->createNamedEntity();
		$properties = array_keys($entity->toArray());
		$where = 1;
		foreach ($criteria as $criterion=>$value) {
			if (!in_array($criterion, $properties)) {
				throw new InvalidArgumentException('Invalid property '.$criterion);
			}
			$where .= " AND $criterion = ".$this->db->quote($value);
		}
		$table = $this->tableName();
		$stm = $this->db->query("SELECT * FROM $table WHERE $where");
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($entity));
		return $stm->fetchAll();
	}

}
