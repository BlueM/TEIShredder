<?php

namespace TEIShredder;

use \InvalidArgumentException;

require 'FactoryInterface.php';

/**
 * Default factory for creating objects.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DefaultFactory implements FactoryInterface {

	/**
	 * @return NamedEntity
	 */
	public function createNamedEntity() {
		return new NamedEntity;
	}

	/**
	 * @return NamedEntityGateway
	 */
	public function createNamedEntityGateway() {
		return new NamedEntityGateway;
	}

}