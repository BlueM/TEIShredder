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
 * Interface for all factories
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface FactoryInterface
{

    /**
     * Creates and returns a NamedEntity object
     *
     * @return NamedEntity
     */
    public function createNamedEntity();

    /**
     * Instantiates and returns a new PlaintextConverter object
     *
     * @return PlaintextConverter
     */
    public function createPlaintextConverter();

    /**
     * Instantiates and returns a new TitleExtractor object
     *
     * @return TitleExtractor
     */
    public function createTitleExtractor();

    /**
     * Creates and returns a gateway for NamedEntity objects
     *
     * @return NamedEntityGateway
     */
    public function createNamedEntityGateway();

    /**
     * Creates and returns a Page object
     *
     * @return Page
     */
    public function createPage();

    /**
     * Creates and returns a gateway for Page objects
     *
     * @return PageGateway
     */
    public function createPageGateway();

    /**
     * Creates and returns a Section object
     *
     * @return Section
     */
    public function createSection();

    /**
     * Creates and returns a gateway for Section objects
     *
     * @return SectionGateway
     */
    public function createSectionGateway();

    /**
     * Creates and returns a Volume object
     *
     * @return Volume
     */
    public function createVolume();

    /**
     * Creates and returns a gateway for Volume objects
     *
     * @return VolumeGateway
     */
    public function createVolumeGateway();

    /**
     * Creates and returns an Element object
     *
     * @return Element
     */
    public function createElement();

    /**
     * Creates and returns a gateway for Element objects
     *
     * @return ElementGateway
     */
    public function createElementGateway();

    /**
     * Creates and returns an XMLChunk object
     *
     * @return XMLChunk
     */
    public function createXMLChunk();

    /**
     * Creates and returns a gateway for XMLChunk objects
     *
     * @return XMLChunkGateway
     */
    public function createXMLChunkGateway();
}
