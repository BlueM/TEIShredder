#!/usr/bin/php
<?php

use \TEIShredder\Setup;
use \TEIShredder\XMLReader;
use \TEIShredder\Indexer_Chunker;
use \TEIShredder\Indexer_Extractor;
use \TEIShredder\PageDataMapper;
use \TEIShredder\VolumeDataMapper;

require __DIR__.'/autoload.php';

$path = __DIR__.'/test/Sample-1.xml';

$xml = file_get_contents($path);

$db = new PDO('sqlite:'.__DIR__.'/test.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$setup = new Setup($db);

// Create an \TEIShredder\XMLReader instance to
// be used by the extractor and the chunker.
$xmlreader = new XMLReader;

// Create chunks and structure
$chunker = new Indexer_Chunker($setup, $xmlreader, $xml);
$chunker->process();

// Extract more information
$extractor = new Indexer_Extractor($setup, $xmlreader, $xml);
$extractor->process();


// Show some information we have collected using the code above
echo "Some information on ".basename($path).":\n";

// 1) Volumes
$volumes = VolumeDataMapper::findAll($setup);
printf(
	"* Document consists of %d volumes\n",
	count($volumes)
);
foreach ($volumes as $volume) {
	printf(
		"  * Volume %d (“%s”) starts on page %d\n",
		$volume->number,
		$volume->title,
		$volume->pagenumber
	);
}

// 2) Pages
$pages = PageDataMapper::findAll($setup);
printf(
	"* Document contains %d pages (i.e.: %d <pb /> elements)\n",
	count($pages),
	count($pages)
);
foreach ($pages as $page) {
	printf(
		"  * Page %-2d (volume %d): @n = “%s”, @xml:id = “%s”\n",
		$page->number,
		$page->volume,
		$page->n,
		$page->xmlid
	);
}

