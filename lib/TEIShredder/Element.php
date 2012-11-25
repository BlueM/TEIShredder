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

use \LogicException;

/**
 * Model class for any XML element in the underlying TEI document that is
 * addressable (i.e.: that has an
 * @xml:id attribute).
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int $xmlid
 * @property int $element
 * @property int $page
 * @property int $chunk
 */
class Element extends Model
{

    /**
     * @xml:id value
     * @var int
     */
    protected $xmlid;

    /**
     * Element name (tag)
     *
     * @var int
     */
    protected $element;

    /**
     * Page number
     *
     * @var string
     * @todo Redundant: element is assigned to a section, and sections know their page
     */
    protected $page;

    /**
     * ID of the chunk which contains this element's start
     *
     * @var int
     */
    protected $chunk;

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
        foreach (array('xmlid', 'element', 'page', 'chunk') as $property) {
            if (is_null($this->$property) or
                '' === $this->$property
            ) {
                throw new LogicException("Integrity check failed: $property cannot be empty.");
            }
        }
        return $this->toArray();
    }

}
