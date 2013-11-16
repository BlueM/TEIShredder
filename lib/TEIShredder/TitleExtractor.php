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
 * Class for extracting the title from a given piece of TEI.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class TitleExtractor
{
    /**
     * @var PlaintextConverter
     */
    protected $plaintextConverter;

    /**
     * @param PlaintextConverter $converter
     */
    public function __construct(PlaintextConverter $converter)
    {
        $this->plaintextConverter = $converter;
    }

    /**
     * Extracts the title from the given piece of XML, i.e. extracting the
     * first <head> node converting it to plaintext.
     *
     * @param $xml
     *
     * @return string
     */
    public function extractTitle($xml)
    {
        $sx = new \SimpleXMLElement($xml);

        if (!isset($sx->head[0])) {
            return '';
        }

        $headXml = $sx->head[0]->asXml();
        return $this->plaintextConverter->convert($headXml);
    }
}
