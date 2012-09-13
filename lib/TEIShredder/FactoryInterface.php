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
	 * @return NamedEntity
	 */
	public function createNamedEntity();

	/**
	 * @return NamedEntityGateway
	 */
	public function createNamedEntityGateway();

	/**
	 * @return Page
	 */
	public function createPage();

	/**
	 * @return PageGateway
	 */
	public function createPageGateway();

	/**
	 * @return Section
	 */
	public function createSection();

	/**
	 * @return SectionGateway
	 */
	public function createSectionGateway();

	/**
	 * @return Volume
	 */
	public function createVolume();

	/**
	 * @return VolumeGateway
	 */
	public function createVolumeGateway();

	/**
	 * @return Element
	 */
	public function createElement();

	/**
	 * @return ElementGateway
	 */
	public function createElementGateway();

	/**
	 * @return XMLChunk
	 */
	public function createXMLChunk();

	/**
	 * @return XMLChunkGateway
	 */
	public function createXMLChunkGateway();

}

