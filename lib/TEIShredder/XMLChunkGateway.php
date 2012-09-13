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
	 * @return string Table name, without the configured prefix
	 */
	public static function tableName() {
		return 'xmlchunk';
	}

	/**
	 * Returns all chunks that are on a given page.
	 * @param Setup $setup
	 * @param int $page Page number
	 * @return XMLChunk[]
	 */
	public static function findByPageNumber(Setup $setup, $page) {
		$table = $setup->prefix.self::tableName();
		$stm = $setup->database->prepare(
			"SELECT id, page, milestone, prestack, xml, poststack, section, plaintext ".
		    "FROM $table WHERE xml != '' AND page = ? ORDER BY id"
		);
		$stm->execute(array($page));
		$stm->setFetchMode(PDO::FETCH_CLASS, '\TEIShredder\XMLChunk', array($setup));
		return $stm->fetchAll();
	}

}
