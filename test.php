#!/usr/bin/php
<?php

set_include_path(__DIR__.'/TEIShredder');

require_once 'TEIShredder/Setup.php';
require_once 'TEIShredder/Indexer.php';
require_once 'TEIShredder/Indexer/Chunker.php';
require_once 'TEIShredder/Indexer/Extractor.php';
require_once 'TEIShredder/XMLReader.php';

$xml = file_get_contents(__DIR__.'/_TESTS/Sample-1.xml');

$db = new PDO('sqlite:'.__DIR__.'/test.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$setup = new TEIShredder_Setup($db);

// Create chunks and structure
$chunker = new TEIShredder_Indexer_Chunker($setup, $xml);
$chunker->process();

// Extract more information
$extractor = new TEIShredder_Indexer_Extractor($setup, $xml);
$extractor->process();

