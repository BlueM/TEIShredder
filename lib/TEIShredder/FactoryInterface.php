<?php

namespace TEIShredder;

/**
 * Interface for all factories
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface FactoryInterface {

	/**
     * Creates and returns a NamedEntity object
	 * @return NamedEntity
	 */
	public function createNamedEntity();

	/**
     * Creates and returns a gateway for NamedEntity objects
	 * @return NamedEntityGateway
	 */
	public function createNamedEntityGateway();

	/**
     * Creates and returns a Page object
	 * @return Page
	 */
	public function createPage();

	/**
     * Creates and returns a gateway for Page objects
	 * @return PageGateway
	 */
	public function createPageGateway();

	/**
     * Creates and returns a Section object
	 * @return Section
	 */
	public function createSection();

	/**
     * Creates and returns a gateway for Section objects
	 * @return SectionGateway
	 */
	public function createSectionGateway();

	/**
     * Creates and returns a Volume object
	 * @return Volume
	 */
	public function createVolume();

	/**
     * Creates and returns a gateway for Volume objects
	 * @return VolumeGateway
	 */
	public function createVolumeGateway();

	/**
     * Creates and returns an Element object
	 * @return Element
	 */
	public function createElement();

	/**
     * Creates and returns a gateway for Element objects
	 * @return ElementGateway
	 */
	public function createElementGateway();

	/**
     * Creates and returns an XMLChunk object
	 * @return XMLChunk
	 */
	public function createXMLChunk();

	/**
     * Creates and returns a gateway for XMLChunk objects
	 * @return XMLChunkGateway
	 */
	public function createXMLChunkGateway();

}

