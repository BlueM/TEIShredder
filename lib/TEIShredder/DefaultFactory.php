<?php

namespace TEIShredder;

use \InvalidArgumentException;

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

	/**
	 * @return Page
	 */
	public function createPage() {
		return new Page;
	}

	/**
	 * @return PageGateway
	 */
	public function createPageGateway() {
		return new PageGateway;
	}

}