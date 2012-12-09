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
 * Model class for sections in the underlying TEI document.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int    $id
 * @property int    $volume
 * @property string $title
 * @property int    $page
 * @property int    $level
 * @property string $element
 * @property string $xmlid
 */
class Section extends Model
{

    /**
     * Section ID
     *
     * @var int
     */
    protected $id;

    /**
     * Volume number
     *
     * @var int
     */
    protected $volume;

    /**
     * Section's title
     *
     * @var string
     */
    protected $title;

    /**
     * Number of the page on which this sections starts
     *
     * @var int
     */
    protected $page;

    /**
     * Section's level (1-based) in the section hierarchy
     *
     * @var int
     */
    protected $level;

    /**
     * Section's element (tag) name
     *
     * @var int
     */
    protected $element;

    /**
     * Section's opening tag's @xml:id attribute value
     * @var string
     */
    protected $xmlid;

    /**
     * Returns data to be passed to a persistence layer.
     *
     * @return array Associative array of property=>value pairs
     * @throws LogicException
     */
    public function persistableData()
    {
        // Basic integrity check
        foreach (array('id', 'volume', 'page', 'level', 'element') as $property) {
            if (is_null($this->$property) ||
                '' === $this->$property
            ) {
                throw new LogicException("Integrity check failed: $property cannot be empty.");
            }
        }
        return $this->toArray();
    }
}
