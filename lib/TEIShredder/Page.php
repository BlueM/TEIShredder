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
 * Model class for physical pages in the underlying TEI document.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int    $number
 * @property string $xmlid
 * @property string $n
 * @property string $rend
 * @property int    $volume
 * @property string $plaintext
 */
class Page extends Model
{

    /**
     * Page number. (Numerical unique number, not the "label"
     * that might have been encoded into @n)
     * @var int
     */
    protected $number;

    /**
     * Value of <pb />'s @xml:id attribute value
     * @var string
     */
    protected $xmlid;

    /**
     * Value of <pb />'s @n attribute value
     * @var string
     */
    protected $n;

    /**
     * Value of <pb />'s @rend attribute value
     * @var int
     */
    protected $rend;

    /**
     * Volume number
     *
     * @var int
     */
    protected $volume;

    /**
     * Plaintext
     *
     * @var int
     */
    protected $plaintext;

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
        foreach (array('number', 'volume') as $property) {
            if (0 >= intval($this->$property)) {
                throw new LogicException("Integrity check failed: $property cannot be empty.");
            }
        }

        return $this->toArray();
    }
}
