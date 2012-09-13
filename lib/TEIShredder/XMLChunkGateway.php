<?php

namespace TEIShredder;

use \InvalidArgumentException;
use \PDO;

/**
 * Gateway for XML chunks
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class XMLChunkGateway extends AbstractGateway {

	/**
	 * Returns the gateway's database table name
	 * @return string
	 */
	public function tableName() {
		return $this->prefix.'xmlchunk';
	}

	/**
	 * Returns all chunks that are on a given page.
	 * @param int $page Page number
	 * @return XMLChunk[]
	 */
	public function findByPageNumber($page) {
		$table = $this->tableName();
		$stm = $this->db->prepare(
			"SELECT id, page, milestone, prestack, xml, poststack, section, plaintext ".
		    "FROM $table WHERE xml != '' AND page = ? ORDER BY id"
		);
		$stm->execute(array($page));
		$chunk = $this->factory->createXMLChunk();
		$stm->setFetchMode(PDO::FETCH_CLASS, get_class($chunk));
		return $stm->fetchAll();
	}

}
