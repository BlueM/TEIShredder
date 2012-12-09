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

use RuntimeException;

/**
 * XMLReader subclass which adds a convenience method.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class XMLReader extends \XMLReader
{

    /**
     * Returns the full open tag for the current element
     *
     * @return string Opening tag including attributes
     * @throws RuntimeException
     */
    public function nodeOpenString()
    {
        if (XMLREADER::ELEMENT != $this->nodeType) {
            throw new RuntimeException('This node is not an opening element.');
        }
        $str = $this->prefix.$this->localName;
        if ($this->hasAttributes) {
            $this->moveToFirstAttribute();
            do {
                $attr = ($this->prefix ? $this->prefix.':' : '').$this->localName;
                $str .= ' '.$attr.'="'.htmlspecialchars($this->value).'"';
            } while ($this->moveToNextAttribute());
            $this->moveToElement();
        }
        if ($this->isEmptyElement) {
            $str .= '/';
        }
        return "<$str>";
    }
}
