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

use InvalidArgumentException;
use PDO;

/**
 * Abstract base class for all gateway classes
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractGateway
{

    /**
     * @var PDO $db
     */
    protected $db;

    /**
     * @var string $prefix
     */
    protected $prefix;

    /**
     * @var FactoryInterface $db
     */
    protected $factory;

    /**
     * Returns the gateway's database table name
     *
     * @return string Table name, including the configured prefix
     */
    abstract protected function tableName();

    /**
     * Constructor.
     *
     * @param PDO              $db
     * @param FactoryInterface $factory
     * @param string           $prefix
     */
    public function __construct(PDO $db, FactoryInterface $factory, $prefix = '')
    {
        $this->db      = $db;
        $this->factory = $factory;
        $this->prefix  = $prefix;
    }

    /**
     * Saves the model
     *
     * @param Model $obj
     */
    public function save(Model $obj)
    {
        $table   = $this->tableName();
        $data    = $obj->persistableData();
        $columns = join(', ', array_keys($data));
        $values  = array_values($data);
        $stm     = $this->db->prepare(
            "INSERT INTO $table ($columns) VALUES (".trim(
                str_repeat('?, ', count($values)),
                ', '
            ).')'
        );
        $stm->execute($values);
    }

    /**
     * Removes all data in the domain
     */
    public function flush()
    {
        $table = $this->tableName();
        $this->db->exec("DELETE FROM ".$table);
    }

    /**
     * @param string $class      Name of the class the returned instances should have.
     * @param array  $properties Indexed array of properties that may be searched
     * @param string $orderby    [optional] String/column list to be used in "ORDER
     *                           BY" statement (not including "ORDER BY").
     * @param mixed  $filters    [optional] Filter string or array of filter strings,
     *                           each in the form of "property operator value", where
     *                           the property can be any of the returned instances'
     *                           instance variables, the operator can be one of < >
     *                           <> >= <= != = == ~  The value can not be quoted
     *                           and if it should be an empty string, it should
     *                           simply be left out (e.g. "title !=").
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function performFind(
        $class,
        array $properties,
        $orderby = '',
        array $filters = array()
    ) {

        $where         = 1;
        $operators     = array('<>', '>=', '<=', '<', '>', '==', '=', '!=', '~');
        $operatormatch = join('|', array_map('preg_quote', $operators));

        foreach ($filters as $filter) {

            if (!preg_match(
                "#^([a-z]+[a-z0-9_]*)\s*($operatormatch)\s*?(.*)$#i",
                ltrim("$filter "),
                $matches
            )
            ) {
                throw new InvalidArgumentException("Unable to parse filter “".$filter."”");
            }

            list(, $property, $operator, $value) = $matches;

            if (!in_array($property, $properties)) {
                throw new InvalidArgumentException("Invalid property $property in “".$filter."”");
            }

            if (!in_array($operator, $operators)) {
                // @codeCoverageIgnoreStart
                // Theoretically, this can never happen, as there is no $operator
                // when the RegExp fails. This condition is just a safety net.
                throw new InvalidArgumentException("Invalid operator $operator in “".$filter."”");
                // @codeCoverageIgnoreEnd
            }

            if ($operator == '!=') {
                $operator = '<>';
            }

            if ($operator == '==') {
                $operator = '=';
            }

            if ($operator == '~') {
                $where .= " AND $property LIKE ".$this->db->quote('%'.trim($value).'%');
            } else {
                $where .= " AND $property $operator ".$this->db->quote(trim($value));
            }
        }

        $sql = "SELECT * FROM ".$this->tableName()." WHERE $where";
        if ($orderby) {
            $sql .= " ORDER BY $orderby";
        }
        $stm = $this->db->query($sql);
        $stm->setFetchMode(PDO::FETCH_CLASS, $class);
        return $stm->fetchAll();
    }
}
