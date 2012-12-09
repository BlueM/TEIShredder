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

use LogicException;

/**
 * Model class for named entities in the underlying TEI document.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int    $xmlid
 * @property int    $page
 * @property string $domain
 * @property string $identifier
 * @property string $contextstart
 * @property string $notation
 * @property string $contextend
 * @property int    $chunk
 * @property string $notationhash
 */
class NamedEntity extends Model
{

    /**
     * Enclosing tag's @xml:id value
     * @var int
     */
    protected $xmlid;

    /**
     * Page number
     *
     * @var string
     */
    protected $page;

    /**
     * Object's domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Value of element's @key attribute in underlying document. Usually some
     * kind of identifier (database record) or Semantic Web URI.
     * @var string
     */
    protected $identifier;

    /**
     * Context after the notation.
     *
     * @var int
     */
    protected $contextend;

    /**
     * The exact string used in the text to refer to the entity
     *
     * @var string
     */
    protected $notation;

    /**
     * Context before the notation.
     *
     * @var int
     */
    protected $contextstart;

    /**
     * ID of the chunk which contains this entity's start
     *
     * @var int
     */
    protected $chunk;

    /**
     * Shortened hash of the lowercased notation
     *
     * @var string
     */
    protected $notationhash;

    /**
     * Returns an associative array of property=>value pairs to be
     * processed by a persistence layer.
     *
     * @return array
     * @throws LogicException
     */
    public function persistableData()
    {
        // Basic integrity check
        foreach (array('page', 'domain', 'identifier', 'notation') as $property) {
            if (is_null($this->$property) ||
                '' === $this->$property
            ) {
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

        return $this->toArray();
    }

    /**
     * Magic method for setting protected object properties from outside.
     *
     * @param string $name  Property name
     * @param mixed  $value Value to be assigned
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);

        $length = 100;
        $omission = '…';

        if ('notation' == $name) {
            $this->notationhash = substr(
                md5(mb_convert_case(trim($this->notation), MB_CASE_LOWER)),
                0,
                10
            );
            return;
        }

        if ('contextstart' == $name) {
            if (mb_strlen($this->contextstart) >= $length &&
                false !== $pos = strrpos($this->contextstart, ' ', -$length)
            ) {
                // Limit length of the context start
                $this->contextstart = $omission.substr($this->contextstart, $pos);
            }
            return;
        }

        if ('contextend' == $name) {
            if (mb_strlen($this->contextend) >= $length &&
                false !== $pos = strpos($this->contextend, ' ', $length)
            ) {
                // Limit length of the context end
                $this->contextend = substr($this->contextend, 0, $pos).$omission;
            }
        }
    }
}
