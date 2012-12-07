#!/usr/bin/php
<?php

require __DIR__.'/autoload.php';

$path = __DIR__.'/test/Sample-1.xml';

mb_internal_encoding('utf8');

$xml = file_get_contents($path);

// Init the database connection
$db = new PDO('sqlite:'.__DIR__.'/test.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create the Setup/configuration/DI object
$setup = new \TEIShredder\Setup($db);

// Create an \TEIShredder\XMLReader instance to
// be used by the extractor and the chunker.
$xmlreader = new \TEIShredder\XMLReader;

// Create XML chunks and track the document structure
$chunker = new \TEIShredder\Indexer\Chunker($setup, $xmlreader, $xml);
$chunker->process();

// Locate named entities and index occurrences of elements
$extractor = new \TEIShredder\Indexer\Extractor($setup, $xmlreader, $xml);
$extractor->process();


// Done. Now, Show some information we have collected using the code above


echo "\nSome information on ".basename($path).":\n";

// 1) Volumes
$volumes = $setup->factory->createVolumeGateway()->find();
printf(
    "\n* Document consists of %d volumes:\n",
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
$pages = $setup->factory->createPageGateway()->find();
printf(
    "\n* Document contains %d pages (i.e.: %d <pb /> elements):\n",
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
    $sections = $setup->factory->createSectionGateway()->find(
        'volume = '.$volume->number,
        'title !='
    );
    printf(
        "\n* Document contains %d sections in volume %d:\n",
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
$entities = $setup->factory->createNamedEntityGateway()->find();
printf(
    "\n* Document contains %d occurrences of tagged named entities:\n",
    count($entities)
);
foreach ($entities as $entity) {
    $text = 'Text extract: “'.$entity->contextstart.mb_convert_case(
        $entity->notation,
        MB_CASE_UPPER
    ).$entity->contextend.'”';
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
