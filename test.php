#!/usr/bin/php
<?php

require __DIR__.'/autoload.php';

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

