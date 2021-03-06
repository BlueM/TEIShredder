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

/**
 * Gateway for volume objects
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class NamedEntityGateway extends AbstractGateway
{

    /**
     * {@inheritDoc}
     */
    protected function tableName()
    {
        return $this->prefix.'entity';
    }

    /**
     * Returns NamedEntity objects matching the given filters.
     *
     * Any number of strings can be passed as arguments, each of which
     * has to be in the form of "property operator value", where the
     * property can be any of the returned instances' instance variables,
     * the operator can be one of < > <> >= <= != = == ~  The value must
     * not be quoted and if it should be an empty string, it should
     * simply be left out (e.g. "title !=").
     *
     * @return NamedEntity[]
     */
    public function find()
    {
        $entity     = $this->factory->createNamedEntity();
        $properties = array_keys($entity->toArray());
        return parent::performFind(get_class($entity), $properties, '', func_get_args());
    }

    /**
     * Returns all distinct notations for the object identified
     * by the argument(s).
     *
     * @param $domain
     * @param $identifier
     *
     * @return array Indexed array of indexed arrays, each with the full
     *               notation in index 0, the number of occurrences in
     *               index 1 and the notation hash in index 2.
     */
    public function findDistinctNotations($domain, $identifier)
    {
        $table = $this->tableName();
        $stm   = $this->db->prepare(
            "SELECT * FROM $table WHERE domain = ? AND identifier = ?"
        );
        $stm->execute(array($domain, $identifier));

        $notations = $count = array();
        foreach ($stm->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $key = mb_convert_case(trim($row['notation']), MB_CASE_LOWER);
            if (empty($notations[$key])) {
                $notations[$key] = array(
                    $row['notation'],
                    0,
                    $row['notationhash']
                );
            }
            $notations[$key][1]++;
        }

        ksort($notations);
        return array_values($notations);
    }
}
