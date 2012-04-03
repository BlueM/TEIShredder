<?php

define('TESTDIR', __DIR__);

require_once __DIR__.'/../TEIShredder/Setup.php';
require_once __DIR__.'/../TEIShredder/Text.php';
require_once __DIR__.'/../TEIShredder/TextChunk.php';
require_once __DIR__.'/../TEIShredder/Indexer.php';
require_once __DIR__.'/../TEIShredder/Indexer/Extractor.php';
require_once __DIR__.'/../TEIShredder/Indexer/Chunker.php';
require_once __DIR__.'/../TEIShredder/XMLReader.php';


#todo
function prepare_default_data() {

	$setup = new TEIShredder_Setup(
		new PDO('sqlite::memory:'),
		'prefix_'
	);

	$setup->database->query('
		CREATE TABLE "prefix_element" (
			"xmlid" TEXT PRIMARY KEY,
			"element" TEXT NOT NULL,
			"page" INTEGER NOT NULL,
			"chunk" INTEGER NOT NULL,
			"attrn" TEXT,
			"attrtargetend" TEXT,
			"data" TEXT
		)
	');

	$setup->database->query('
		CREATE TABLE "prefix_structure" (
			"id" INTEGER PRIMARY KEY,
			"volume" INTEGER,
			"title" TEXT,
			"page" INTEGER NOT NULL,
			"level" INTEGER NOT NULL,
			"type" TEXT NOT NULL,
			"xmlid" TEXT
		)
	');

	$setup->database->query('
		CREATE TABLE "prefix_notation" (
			"xmlid" TEXT,
			"page" INTEGER NOT NULL,
			"domain" TEXT NOT NULL,
			"object" INTEGER,
			"notation" TEXT,
			"context" TEXT,
			"container" TEXT,
			"containerid" TEXT,
			"chunk" INTEGER,
			"notationhash" TEXT
		);
	');

	$setup->database->query('
		CREATE TABLE "prefix_page" (
			"page" INTEGER PRIMARY KEY ASC,
			"xmlid" TEXT UNIQUE,
			"volume" INTEGER NOT NULL,
			"plaintext" TEXT,
			"pagenotation" TEXT
		);
	');

	$setup->database->query('
		CREATE TABLE "prefix_volume" (
			  "number" INTEGER PRIMARY KEY ASC,
			  "title" text NOT NULL,
			  "pagenum" INTEGER NOT NULL
		);
	');

	$setup->database->query('
		CREATE TABLE "prefix_xmlchunk" (
			"id" INTEGER PRIMARY KEY ASC,
			"volume" INTEGER NOT NULL,
			"page" INTEGER NOT NULL,
			"col" TEXT,
			"prestack" TEXT,
			"xml" TEXT,
			"poststack" TEXT,
			"plaintext" TEXT,
			"section" INTEGER NOT NULL
		);
	');

	return $setup;
}