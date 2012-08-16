<?php

namespace TEIShredder;

use \PDO;
use \InvalidArgumentException;

/**
 * Class for obtaining info on the source XML document, including
 * text structure, page numbers, page names/notations etc.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class DocumentInfo {

	/**
	/**
	 * Returns all sections as associative array.
	 * @param Setup $setup
	 * @param int $volume Volume number.
	 * @return array Indexed array of associative arrays with keys "id", "page",
	 *               "title", "xmlid", "level", and moreover "children", "first",
	 *               "last" (the last three keys being boolean)
	 */
	public static function fetchStructure(Setup $setup, $volume) {
		$db = $setup->database;
		$res = $db->query("SELECT id, page, title, level, xmlid ".
		                  "FROM ".$setup->prefix.'section '.
		                  "WHERE level > 0 AND volume = ".$db->quote($volume)." ".
		                  "ORDER BY id");
		$sections = $res->fetchAll(PDO::FETCH_ASSOC);
		$lastlevel = 0;

		for ($i = 0, $ii = count($sections); $i < $ii; $i ++) {

			if (empty($sections[$i + 1]) or
			    $sections[$i + 1]['level'] <= $sections[$i]['level']) {
				$sections[$i]['children'] = false;
			} else {
				$sections[$i]['children'] = true;
			}

			if ($sections[$i]['level'] > $lastlevel) {
				$sections[$i]['first'] = true;
			} else {
				$sections[$i]['first'] = false;
			}

			$offset = 1;
			do {

				if (empty($sections[$i + $offset])) {
					$sections[$i]['last'] = true;
					break;
				}

				if ($sections[$i + $offset]['level'] == $sections[$i]['level']) {
					// On same level >> this is not the last one
					$sections[$i]['last'] = false;
					break;
				}

				if ($sections[$i + $offset]['level'] < $sections[$i]['level']) {
					// On upper level >> this is the last one
					$sections[$i]['last'] = true;
					break;
				}

				// Else: subordinate, proceed to next
				$offset ++;

			} while (1);

			$lastlevel = $sections[$i]['level'];
		}

		return $sections;
	}

	/**
	 * Returns the total number of pages in the source text, either for
	 * all volumes or for a specific volume
	 * @param Setup $setup
	 * @param int $volume [optional] Volume number
	 * @return int Number of pages
	 */
	public static function fetchNumberOfPages(Setup $setup, $volume = null) {
		$db = $setup->database;
		$prefix = $setup->prefix;
		$query = 'SELECT MAX(number) FROM '.$prefix.'page';
		if (intval($volume)) {
			$query .= ' WHERE volume = '.intval($volume);
		}
		$sth = $db->query($query);
		return (int)$sth->fetchColumn(0);
	}

	/**
	 * Returns all page numbers and corresponding page notations of
	 * the given volume.
	 * @param Setup $setup
	 * @param int $volume [optional] Volume number.
	 * @return array Associative array with the page number as key and the page
	 *               notation as value.
	 */
	public static function fetchPageNotations(Setup $setup, $volume = null) {
		$db = $setup->database;
		$prefix = $setup->prefix;
		$notations = array();
		$where = $volume ? 'volume = '.$db->quote($volume) : '1';
		$res = $db->query("SELECT number, n FROM ".$prefix."page WHERE $where ORDER BY number");
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$notations[$row[0]] = $row[1];
		}
		return $notations;
	}

	/**
	 * Returns several pages' numbers or titles (whatever @n contains)
	 * @param Setup $setup
	 * @param array|int $nums Indexed array of page numbers.
	 * @return array Associative array, sorted by the page number, with
	 *               the page number as key and @n as the value.
	 */
	public static function fetchNAttributesForPageNumbers(Setup $setup, array $nums) {
		$res = $setup->database->query(
			"SELECT number, n FROM ".$setup->prefix."page ".
			"WHERE number IN (".join(', ', array_map('intval', $nums)).") ORDER BY number"
		);
		$n = array();
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$n[$row[0]] = $row[1];
		}
		return $n;
	}

	/**
	 * Returns info on the given section, on the previous and next section
	 * @param Setup $setup
	 * @param int $section Section ID
	 * @return array Associative array with keys "this", "prev" and "next",
	 *               each one being an associative array itself. Additionally,
	 *               includes a key "volstart" whose value is the number of
	 *               the first page in the current volume.
	 * @throws InvalidArgumentException
	 */
	public static function fetchStructureDataForSection(Setup $setup, $section) {
		$db = $setup->database;
		$structtable = $setup->prefix.'section';
		$pagetable = $setup->prefix.'page';
		$section = $db->quote($section);

		$statements = array(
			'this'=>"id = $section",
			'prev'=>"id < $section AND title != '' ORDER BY id DESC",
			'next'=>"id > $section AND title != '' ORDER BY id ASC",
		);

		$data = array();

		foreach ($statements as $type=>$statement) {
			$sql = "SELECT s.id, s.title, s.volume, s.page, s.xmlid, p.n
                    FROM $structtable AS s, $pagetable AS p
                    WHERE p.number = s.page AND $statement
                    LIMIT 0, 1";
			$res = $db->query($sql);
			$row = $res->fetch(PDO::FETCH_ASSOC);
			if (!$row['title']) {
				$row['title'] = $row['n'];
			}
			$data[$type] = $row;
			$res->closeCursor();
		}

		if (empty($data['this']['page'])) {
			throw new InvalidArgumentException("Invalid section ID $section");
		}

		// Current section's start page
		$res = $db->query("SELECT page FROM $structtable WHERE volume = ".
					      $db->quote($data['this']['volume'])." ORDER BY id LIMIT 1");
		$data['volstart'] = $res->fetchColumn();

		return $data;
	}

}

