<?php

define('TESTDIR', __DIR__);

require_once __DIR__.'/../autoload.php';

/**
 * Creates a new in-memory SQLite database, creates the default
 * tables and returns it, wrapped in a TEIShredder_Setup instance.
 * @return \TEIShredder\Setup
 */
function prepare_default_data() {

	$pdo = new PDO('sqlite::memory:');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$setup = new \TEIShredder\Setup(
		$pdo,
		'prefix_'
	);

	$create = str_replace(
		'<prefix>',
		'prefix_',
		file_get_contents(__DIR__.'/../create-sqlite.sql')
	);

	foreach (preg_split('#\v{2,}#', trim($create)) as $query) {
		if ('-- ' == substr($query, 0, 3)) {
			continue;
		}
		$setup->database->query($query);
	}

	return $setup;
}