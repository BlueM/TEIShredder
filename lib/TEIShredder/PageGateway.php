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
 * Gateway class for page objects
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class PageGateway extends AbstractGateway
{

    /**
     * {@inheritDoc}
     */
    protected function tableName()
    {
        return $this->prefix.'page';
    }

    /**
     * Returns the page object with the given pagenumber.
     *
     * @param int $number
     *
     * @throws InvalidArgumentException
     * @return Page
     */
    public function findByIdentifier($number)
    {
        $table = $this->tableName();
        $stm   = $this->db->prepare("SELECT * FROM $table WHERE number = ?");
        $stm->execute(array($number));
        $stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createPage());
        if (false === $page = $stm->fetch()) {
            throw new InvalidArgumentException('Invalid page number');
        }
        return $page;
    }

    /**
     * Returns the last page object, i.e. the one with the highest page number
     *
     * @return Page
     */
    public function findLastPage()
    {
        $table = $this->tableName();
        $stm   = $this->db->query("SELECT * FROM $table ORDER BY number DESC LIMIT 1");
        $stm->setFetchMode(PDO::FETCH_INTO, $this->factory->createPage());
        return $stm->fetch();
    }

    /**
     * Returns page objects
     *
     * @param array $numbers [optional] Indexed array of page numbers
     *
     * @return Page[]
     */
    public function findMultiple(array $numbers)
    {
        $table = $this->tableName();
        $where = 1;
        if (count($numbers)) {
            $numbers = array_map('intval', $numbers);
            $where .= " AND number IN(".join(', ', $numbers).")";
        }
        $stm  = $this->db->query("SELECT * FROM $table WHERE $where ORDER BY number");
        $page = $this->factory->createPage();
        $stm->setFetchMode(PDO::FETCH_CLASS, get_class($page));
        return $stm->fetchAll();
    }

    /**
     * Returns Page objects matching the given filters.
     *
     * Any number of strings can be passed as arguments, each of which
     * has to be in the form of "property operator value", where the
     * property can be any of the returned instances' instance variables,
     * the operator can be one of < > <> >= <= != = == ~  The value must
     * not be quoted and if it should be an empty string, it should
     * simply be left out (e.g. "title !=").
     *
     * @return Page[]
     */
    public function find()
    {
        $page       = $this->factory->createPage();
        $properties = array_keys($page->toArray());
        return parent::performFind(get_class($page), $properties, 'number', func_get_args());
    }
}
