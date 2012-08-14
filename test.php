#!/usr/bin/php
<?php

set_include_path(__DIR__.'/TEIShredder');

require_once 'lib/TEIShredder/Setup.php';
require_once 'lib/TEIShredder/Indexer.php';
require_once 'lib/TEIShredder/Indexer/Chunker.php';
require_once 'lib/TEIShredder/Indexer/Extractor.php';
require_once 'lib/TEIShredder/XMLReader.php';

$xml = file_get_contents(__DIR__.'/test/Sample-1.xml');

$db = new PDO('sqlite:'.__DIR__.'/test.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$setup = new \TEIShredder\Setup($db);

// Create an \TEIShredder\XMLReader instance to
// be used by the extractor and the chunker.
$xmlreader = new \TEIShredder\XMLReader;

// Create chunks and structure
$chunker = new \TEIShredder\Indexer_Chunker($setup, $xmlreader, $xml);
$chunker->process();

// Extract more information
$extractor = new \TEIShredder\Indexer_Extractor($setup, $xmlreader, $xml);
$extractor->process();

