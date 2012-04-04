#!/usr/local/bin/php
<?php

set_include_path(__DIR__.'/TEIShredder');

require_once 'TEIShredder/Setup.php';
require_once 'TEIShredder/Indexer.php';
require_once 'TEIShredder/XMLReader.php';
require_once 'TEIShredder/Indexer/Chunker.php';
require_once 'TEIShredder/Indexer/Extractor.php';

$xmlpath = __DIR__.'/Sample-1.xml';

$db = new PDO('sqlite:'.__DIR__.'/teishredder.sqlite');

//$db = new PDO('mysql:host=localhost;dbname=sandrart', 'wwwrun', 'runwww');
//$db->exec("SET NAMES 'utf8'");
//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$setup = new TEIShredder_Setup(
	$db,             // PDO instance
	'la_',           // Prefix
	'xmlToPlainText' // Callback function for plaintext conversion
);

// Create chunks
$chunker = new TEIShredder_Indexer_Chunker($setup, $xmlpath);
$chunker->process();

//$extractor = new TEIShredder_Indexer_Extractor($setup, $xmlpath);
//$extractor->process();



/**
 * Returns a plaintext representation from the given TEI source fragment
 * that is suitable for our purposes (extraction of titles and small text
 * parts)
 * @param string $xml XML string
 * @return string UTF-8 Plaintext
 */
function xmlToPlainText($xml) {

	// Remove any <note> tags added by project members
	$xml = preg_replace('!<note[^>]*?resp="#uid\d+"[^>]*?'.'>.*?</note>!s', ' ', $xml);

	#todo Currently, any remaining <note> tags are left where they are in the source text.
	# This way, they could be in the way for phrase searches.

	// Remove hyphenation at EOL when character after hyphen is lowercase
	$xml = preg_replace('#-<lb */>\s+([a-z])#', '$1', $xml);
	// Also remove hyphenation if the whole word is uppercase
	$xml = preg_replace('#([A-Z]+)-<lb */>\s+([A-Z]+)#', '$1$2', $xml);

	$xml = preg_replace('#<lb\s*/\s*>#s', ' ', $xml);
	$xml = preg_replace('#<sic\b[^>]*>.*?</sic>#s', ' ', $xml);
	$xml = preg_replace('#<orig\b[^>]*>.*?</orig>#s', ' ', $xml);
	$xml = preg_replace('#<del\b[^>]*>.*?</del>#s', ' ', $xml);
	$xml = strip_tags($xml);
	$xml = html_entity_decode($xml, ENT_COMPAT, 'UTF-8');

	// Next line: NOT \s+
	$xml = preg_replace('#[ \t\r\n]+#', ' ', trim($xml));

	return $xml;
}
