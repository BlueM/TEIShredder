<?php

/**
 * Class for obtaining info on the source XML document, including
 * text structure, page numbers, page names/notations etc.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link http://www.sandrart.net/
 * @version SVN: $Id: Text.php 1289 2012-03-20 15:17:53Z cb $
 */
class TEIShredder_Text {

	/**
	 * Returns the numerical page number for an xml:id attribute
	 * value in the source text.
	 * @param TEIShredder_Setup $setup
	 * @param string $elmntid XML element ID
	 * @return int|false Page number or false, if there's no such xml:id.
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
	 * @return array Indexed array of associative arrays with keys "page",
	 *               "title", 'xmlid' and "level".
	 */
	public static function fetchStructure(TEIShredder_Setup $setup, $volume) {

		$db = $setup->database;
		$res = $db->query("SELECT page, title, level, xmlid ".
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
		$res = $db->query("SELECT page, pagenotation FROM ".$prefix."page WHERE $where ORDER BY page");
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
	 * @param CBDIContainer $dic
	 * @param mixed Page number as int or indexed array of page numbers.
	 * @return mixed If a scalar is passed as argument, returns the page
	 *               notation as string, otherwise an associative array, sorted
	 *               by the page number, with the page number as key and the
	 *               notation as value.
	 */
	public static function fetchPageNotationById(CBDIContainer $dic, $page) {
		$db = $dic->database;
		if (is_scalar($page)) {
			return $db->getOne('SELECT pagenotation FROM '.self::$prefix.'page WHERE page = ?', $page);
		}
		$ids = join(', ', array_map('intval', $page));
		if (empty($ids)) {
			return null;
		}
		$res = $db->query("SELECT page, pagenotation
		                   FROM ".self::$prefix."page
		                   WHERE page IN ($ids)
		                   ORDER BY page");
		$notations = array();
		foreach ($res->fetchAll(PDO::FETCH_NUM) as $row) {
			$notations[$row[0]] = $row[1];
		}
		unset($res, $row);
		return $notations;
	}

	/**
	 * Returns info on the given section, on the previous and next section
	 * @param CBDIContainer $dic
	 * @param int $section Section ID
	 * @return array Associative array with keys "this", "prev" and "next",
	 *               each one being an associative array itself. Additionally,
	 *               includes a key "volstart" whose value is the number of
	 *               the first page in the current volume
	 * @throws InvalidArgumentException
	 */
	public static function fetchStructureDataForSection(CBDIContainer $dic, $section) {
		$db = $dic->database;
		$structtable = self::$prefix.'structure';
		$pagetable = self::$prefix.'page';
		$res = $db->query("(SELECT 'this' AS type, id, title, p.pagenotation,
		                            s.volume, s.page, s.xmlid
		                    FROM $structtable AS s, $pagetable AS p
		                    WHERE p.page = s.page AND
		                    id = ?
		                    LIMIT 0, 1)
		                   UNION
		                   (SELECT 'prev' AS type, id, title, p.pagenotation,
		                            s.volume, s.page, s.xmlid
		                    FROM $structtable AS s, $pagetable AS p
		                    WHERE p.page = s.page AND
		                    id < ? AND title != ''
		                    ORDER BY id DESC
		                    LIMIT 0, 1)
		                   UNION
		                   (SELECT 'next' AS type, id, title, p.pagenotation,
		                            s.volume, s.page, s.xmlid
		                    FROM $structtable AS s, $pagetable AS p
		                    WHERE p.page = s.page AND
		                    id > ? AND title != ''
		                    ORDER BY id
		                    LIMIT 0, 1)
		                   ",
		                   array($section, $section, $section));
		$data = array('this'=>null, 'next'=>null, 'prev'=>null);
		foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$type = $row['type'];
			unset($row['type']);
			if (!$row['title']) {
				$row['title'] = $row['pagenotation'];
			}
			$data[$type] = $row;
		}
		unset($res, $row);
		if (array() === $data) {
			throw new InvalidArgumentException("Invalid section ID $section");
		}

		// Current section's start page
		$data['volstart'] = $db->getOne("SELECT page
		                                 FROM $structtable
		                                 WHERE volume = ?
		                                 ORDER BY id
		                                 LIMIT 1", $data['this']['volume']);

		return $data;
	}

}

