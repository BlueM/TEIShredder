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

}
