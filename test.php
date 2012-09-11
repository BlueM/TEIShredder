#!/usr/bin/php
<?php

use \TEIShredder\Setup;
use \TEIShredder\XMLReader;
use \TEIShredder\Indexer_Chunker;
use \TEIShredder\Indexer_Extractor;
use \TEIShredder\PageGateway;
use \TEIShredder\VolumeGateway;
use \TEIShredder\SectionGateway;
use \TEIShredder\NamedEntityGateway;

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
echo "\nSome information on ".basename($path).":\n";

// 1) Volumes
$volumes = VolumeGateway::findAll($setup);
printf(
	"\n* Document consists of %d volumes\n",
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
$pages = PageGateway::findAll($setup);
printf(
	"\n* Document contains %d pages (i.e.: %d <pb /> elements)\n",
	count($pages),
	count($pages)
);
foreach ($pages as $page) {
	printf(
		"  * Page % 2d (volume %d): @n = “%s”, @xml:id = “%s”\n",
		$page->number,
		$page->volume,
		$page->n,
		$page->xmlid
	);
}

// 3) Sections
foreach ($volumes as $volume) {
	$sections = SectionGateway::findAllInVolume($setup, $volume->number);
	printf(
		"\n* Document contains %d sections in volume %d\n",
		count($sections),
		$volume->number
	);
	foreach ($sections as $section) {
		printf(
			"  * Section % 2d (starting on page % 2d): “%s”\n",
			$section->id,
			$section->page,
			$section->title
		);
	}
}

// 4) Named Entities mentioned in the text
$entities = NamedEntityGateway::findAll($setup);
printf(
	"\n* Document contains %d occurrences of tagged named entities\n",
	count($entities)
);
foreach ($entities as $entity) {
	$text = 'Text extract: “'.$entity->contextstart.mb_convert_case($entity->notation, MB_CASE_UPPER).$entity->contextend.'”';
	$text = str_replace("\n", "\n    ", wordwrap($text, 72));
	printf(
		"  * On page %d, entity of domain “%s” with identifier “%s”\n".
		"    %s\n".
		"    Tag’s @xml:id value: “%s”\n",
		$entity->page,
		$entity->domain,
		$entity->identifier,
		$text,
		$entity->xmlid
	);
}

echo "\n";
