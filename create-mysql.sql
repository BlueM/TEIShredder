-- Please replace “<prefix>” with the desired prefix or remove it

CREATE TABLE `<prefix>element` (
  `xmlid` VARCHAR(24) NOT NULL DEFAULT '' COMMENT 'Unique xml:id attribute of element',
  `element` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Name of XML element',
  `page` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Number of the page which contains this element',
  `chunk` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID of chunk this element belongs to',
  PRIMARY KEY (`xmlid`),
  KEY `element` (`element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tracks elements with xml:id attributes and related data';

CREATE TABLE `<prefix>entity` (
  `xmlid` VARCHAR(20) DEFAULT '' COMMENT 'xml:id attr. of elemnt containing the object occurrence',
  `page` INT(11) UNSIGNED NOT NULL COMMENT 'Page number, 1-based',
  `domain` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Object domain',
  `identifier` VARCHAR(50) DEFAULT '' COMMENT 'Object identifier',
  `contextstart` TEXT COMMENT 'Context before the notation',
  `notation` TEXT NOT NULL COMMENT 'Exact notation/wording used in the text',
  `contextend` TEXT COMMENT 'Context after the notation',
  `chunk` INT(11) UNSIGNED DEFAULT NULL COMMENT 'ID of the chunk',
  `notationhash` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'Truncated MD5 hash of the notation',
  KEY `page` (`page`),
  KEY `domain_identifier` (`domain`, `identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Data on entity occurrences in the text';

CREATE TABLE `<prefix>page` (
  `number` INT(11) NOT NULL DEFAULT '0' COMMENT 'Page number',
  `xmlid` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'xml:id of <pb/> element',
  `volume` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Volume number',
  `plaintext` TEXT NOT NULL COMMENT 'Page contents as plaintext',
  `n` VARCHAR(100) DEFAULT '' COMMENT 'Value of @n attribute',
  `rend` VARCHAR(100) DEFAULT '' COMMENT 'Value of @rend attribute',
  PRIMARY KEY (`number`),
  UNIQUE KEY `xmlid` (`xmlid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains text on a per-page-basis';

CREATE TABLE `<prefix>section` (
  `id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Unique strucuture element ID/PK',
  `volume` INT(11) UNSIGNED NOT NULL COMMENT 'Volume number, 1-based',
  `title` TEXT NOT NULL COMMENT 'Title of the section',
  `page` INT(11) UNSIGNED NOT NULL COMMENT 'Page number (of digitized page, not source text)',
  `level` INT(10) UNSIGNED DEFAULT '0' COMMENT 'Element''s text structure level',
  `element` VARCHAR(20) DEFAULT NULL COMMENT 'Element/tag name',
  `xmlid` VARCHAR(20) DEFAULT NULL COMMENT 'Element''s xml:id attribute value',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Structural text sections';

CREATE TABLE `<prefix>volume` (
  `number` INT(11) UNSIGNED NOT NULL COMMENT 'Volume number, 1-based',
  `title` TEXT NOT NULL COMMENT 'Volume title',
  `pagenumber` INT(11) NOT NULL COMMENT 'Pagenumber the volume starts at',
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Volumes and their titles';

CREATE TABLE `<prefix>xmlchunk` (
  `id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Chunk''s primary key',
  `page` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Number of the page this chunk is on',
  `milestone` VARCHAR(40) DEFAULT '' COMMENT 'Current <milestone /> context',
  `prestack` TEXT NOT NULL COMMENT 'Tags that are opened before this text chunk',
  `xml` TEXT NOT NULL COMMENT 'The chunk''s XML source (not well-formed)',
  `poststack` TEXT NOT NULL COMMENT 'Tags that have to be closed behind this text chunk',
  `plaintext` TEXT NOT NULL COMMENT 'Chunk''s content as plaintext',
  `section` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Section ID',
  PRIMARY KEY (`id`),
  KEY `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Chunked XML document';
