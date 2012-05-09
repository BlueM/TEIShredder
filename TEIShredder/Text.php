<?php

/**
 * Class for obtaining info on the source XML document, including
 * text structure, page numbers, page names/notations etc.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/TEIShredder/
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TEIShredder_Text {

	/**
	 * Returns the numerical page number for an xml:id attribute
	 * value in the source text.
	 * @param TEIShredder_Setup $setup
	 * @param string $elmntid XML element ID
	 * @return int|bool Page number or false, if there's no such xml:id.
	 */
	public static function fetchPageNumberForElementId(TEIShredder_Setup $setup, $elmntid) {
		static $cache = array();
		static $sth = null;
		if (!$sth) {
			$db = $setup->database;
			$sth = $db->prepare('SELECT page FROM '.$setup->prefix.'element WHERE xmlid = ?');
		}
		if (!array_key_exists($elmntid, $cache)) {
			$sth->execute(array($elmntid));
			if (false === $pagenum = $sth->fetchColumn(0)) {
				return false;
			}
			$cache[$elmntid] = $pagenum;
		}
		return (int)$cache[$elmntid];
	}

	/**
	 * Returns all structure entries/sections as associative array.
	 * @param TEIShredder_Setup $setup
	 * @param int $volume Volume number.
	 * @return array Indexed array of associative arrays with keys "id", "page",
	 *               "title", "xmlid", "level", and moreover "children", "first",
	 *               "last" (the last three keys being boolean)
	 */
	public static function fetchStructure(TEIShredder_Setup $setup, $volume) {
		$db = $setup->database;
		$res = $db->query("SELECT id, page, title, level, xmlid ".
		                  "FROM ".$setup->prefix.'structure '.
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
	 * @param TEIShredder_Setup $setup
	 * @param int $volume [optional] Volume number
	 * @return int Number of pages
	 */
	public static function fetchNumberOfPages(TEIShredder_Setup $setup, $volume = null) {
		$db = $setup->database;
		$prefix = $setup->prefix;
		$query = 'SELECT MAX(page) FROM '.$prefix.'page';
		if (intval($volume)) {
			$query .= ' WHERE volume = '.intval($volume);
		}
		$sth = $db->query($query);
		return (int)$sth->fetchColumn(0);
	}

	/**
	 * Returns all page numbers and corresponding page notations of
	 * the given volume.
	 * @param TEIShredder_Setup $setup
	 * @param int $volume [optional] Volume number.
	 * @return array Associative array with the page number as key and the page
	 *               notation as value.
	 */
	public static function fetchPageNotations(TEIShredder_Setup $setup, $volume = null) {
		$db = $setup->database;
		$prefix = $setup->prefix;
		$notations = array();
		$where = $volume ? 'volume = '.$db->quote($volume) : '1';
		$res = $db->query("SELECT page, n FROM ".$prefix."page WHERE $where ORDER BY page");
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$notations[$row[0]] = $row[1];
		}
		return $notations;
	}

	/**
	 * Returns the xml:id value(s) of the <pb /> element, the volume number and
	 * values of @n and @rend attributes of the page with the given page number.
	 * @param TEIShredder_Setup $setup
	 * @param int $pagenum Internal page number
	 * @return array Array with indexes 0 = volume, 1 = xml:id value, 2 = page name.
	 * @throws InvalidArgumentException
	 */
	public static function fetchPageData(TEIShredder_Setup $setup, $pagenum) {
		static $sth = null;
		if (!$sth) {
			$sth = $setup->database->prepare(
				'SELECT volume, xmlid, n, rend FROM '.$setup->prefix."page WHERE page = ?"
			);
		}
		$sth->execute(array($pagenum));
		if (false === $data = $sth->fetch(PDO::FETCH_NUM)) {
			throw new InvalidArgumentException('Invalid page number');
		}
		return $data;
	}

	/**
	 * Returns the volumes' numbers and titles.
	 * @param TEIShredder_Setup $setup
	 * @return array Associative array containing number=>array() pairs,
	 *               where the array has keys "title" and "pagenum"
	 */
	public static function fetchVolumes(TEIShredder_Setup $setup) {
		$volumes = array();
		$res = $setup->database->query(
			'SELECT number, title, pagenum FROM '.$setup->prefix.'volume ORDER BY number'
		);
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$volumes[$row[0]] = array(
				'title'=>$row[1],
				'pagenum'=>$row[2],
			);
		}
		return $volumes;
	}

	/**
	 * Returns the page's unique notation/title by its unique page number
	 * @param TEIShredder_Setup $setup
	 * @param array $nums Indexed array of page numbers.
	 * @return array Associative array, sorted by the page number, with
	 *               the page number as key and @n as the value.
	 */
	public static function fetchNAttributesForPageNumbers(TEIShredder_Setup $setup, array $nums) {
		$db = $setup->database;
		$res = $db->query("SELECT page, n
		                   FROM ".$setup->prefix."page
		                   WHERE page IN (".join(', ', array_map('intval', $nums)).")
		                   ORDER BY page");
		$n = array();
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$n[$row[0]] = $row[1];
		}
		return $n;
	}

	/**
	 * Returns info on the given section, on the previous and next section
	 * @param TEIShredder_Setup $setup
	 * @param int $section Section ID
	 * @return array Associative array with keys "this", "prev" and "next",
	 *               each one being an associative array itself. Additionally,
	 *               includes a key "volstart" whose value is the number of
	 *               the first page in the current volume
	 * @throws InvalidArgumentException
	 * @todo Test fails due to incomplete test isolation.
	 */
	public static function fetchStructureDataForSection(TEIShredder_Setup $setup, $section) {
		$db = $setup->database;
		$structtable = $setup->prefix.'structure';
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
                               WHERE p.page = s.page AND $statement
                               LIMIT 0, 1";
			echo "$sql\n\n";
			$res = $db->query($sql);
			$row = $res->fetch(PDO::FETCH_ASSOC);
			if (!$row['title']) {
				$row['title'] = $row['n'];
			}
			$data[$type] = $row;
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

