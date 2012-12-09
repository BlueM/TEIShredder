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
 * Class for retrieving well-formed XML fragments from the source TEI document.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property int    $id
 * @property string $milestone
 * @property string $page
 * @property string $prestack
 * @property string $xml
 * @property string $poststack
 * @property int    $section
 * @property string $plaintext
 */
class XMLChunk extends Model
{

    /**
     * Chunk ID
     *
     * @var int
     */
    protected $id;

    /**
     * Value of last <milestone/>'s
     * @unit and @n, concatenated
     * @var string
     */
    protected $milestone;

    /**
     * Number of the page this chunk is on
     *
     * @var int
     */
    protected $page;

    /**
     * String of open XML tags at the point in the source document
     * where this chunk of XML starts.
     *
     * @var string
     */
    protected $prestack;

    /**
     * Chunk's XML source (probably not well-formed)
     *
     * @var string
     */
    protected $xml;

    /**
     * XML tags to be closed after this chunk of XML.
     *
     * @var
     */
    protected $poststack;

    /**
     * ID of the chunk's section in the text structure
     *
     * @var int
     */
    protected $section;

    /**
     * Chunk's plaintext representation
     *
     * @var string
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
        foreach (array('page', 'section') as $property) {
            if (is_null($this->$property) ||
                '' === $this->$property
            ) {
                throw new LogicException("Integrity check failed: $property cannot be empty.");
            }
        }
        return $this->toArray();
    }

    /**
     * Returns the chunk's content source as well-formed XML, i.e.: with
     * pre-stack and post-stack tags added, with Unix-style line endings.
     *
     * @param bool $noxmlid [optional] If true (default: false), @xml:id attributes
     *                      are removed from pre-stack tags.
     *
     * @return string
     */
    public function getWellFormedXML($noxmlid = false)
    {
        if ($noxmlid) {
            $prestack = preg_replace('#\s+xml:id=".*?"#', '', $this->prestack);
        } else {
            $prestack = $this->prestack;
        }
        return str_replace(array("\r\n", "\n"), "\n", $prestack.$this->xml.$this->poststack);
    }
}
