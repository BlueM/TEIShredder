-- Please replace “<prefix>” with the desired prefix or remove it

CREATE TABLE `<prefix>element` (
  `xmlid` varchar(24) NOT NULL DEFAULT '' COMMENT 'Unique xml:id attribute of element',
  `element` varchar(20) NOT NULL DEFAULT '' COMMENT 'Name of XML element',
  `page` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of page that this element is on',
  `chunk` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID of chunk this element belongs to',
  `attrn` varchar(20) DEFAULT '' COMMENT 'Value of tei:n attribute, if present',
  `attrtargetend` varchar(30) DEFAULT '' COMMENT 'Value of tei:targetEnd attribute',
  `data` text COMMENT 'Element-dependend data, e.g. @indexName value or JSONed array',
  PRIMARY KEY (`xmlid`),
  KEY `attrn` (`attrn`),
  KEY `element` (`element`),
  KEY `attrtargetend` (`attrtargetend`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tracks elements with xml:id attributes and related data';

CREATE TABLE `<prefix>notation` (
  `xmlid` varchar(20) DEFAULT '' COMMENT 'xml:id attr. of elemnt containing the object occurrence',
  `page` int(11) unsigned NOT NULL COMMENT 'Page number, 1-based',
  `domain` varchar(20) NOT NULL DEFAULT '' COMMENT 'Object domain',
  `key` int(11) unsigned DEFAULT '0' COMMENT 'Object identifier',
  `notation` text NOT NULL COMMENT 'The exact string used in the text',
  `context` text COMMENT 'Text context; notation position is marked by <$>',
  `container` varchar(16) DEFAULT NULL COMMENT 'Type (tag) of container',
  `containerid` varchar(20) DEFAULT NULL COMMENT 'Container element xml:id (may be empty)',
  `chunk` int(11) unsigned DEFAULT NULL COMMENT 'ID of the chunk in the chunk table',
  `notationhash` varchar(16) NOT NULL DEFAULT '' COMMENT 'Truncated MD5 hash of the notation',
  KEY `page` (`page`),
  KEY `container` (`container`),
  KEY `domain` (`domain`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Data on entity occurrences in the text';

CREATE TABLE `<prefix>page` (
  `number` int(11) NOT NULL DEFAULT '0' COMMENT 'Page number',
  `xmlid` varchar(40) NOT NULL DEFAULT '' COMMENT 'xml:id of <pb/> element',
  `volume` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Volume number',
  `plaintext` text NOT NULL COMMENT 'Chunk contents as plaintext',
  `n` varchar(100) DEFAULT '' COMMENT 'Value of @n attribute',
  `rend` varchar(100) DEFAULT '' COMMENT 'Value of @rend attribute',
  `facs` varchar(40) DEFAULT '' COMMENT 'Value of @facs attribute',
  PRIMARY KEY (`page`),
  UNIQUE KEY `xmlid` (`xmlid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains text on a per-page-basis';

CREATE TABLE `<prefix>structure` (
  `id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Unique strucuture element ID/PK',
  `volume` int(11) unsigned NOT NULL COMMENT 'Volume number',
  `title` text NOT NULL COMMENT 'Title of the structure element',
  `page` int(11) unsigned NOT NULL COMMENT 'Page number (of digitized page, not source text)',
  `level` int(10) unsigned DEFAULT '0' COMMENT 'Element''s text structure level',
  `element` varchar(20) DEFAULT NULL COMMENT 'Element/tag name',
  `xmlid` varchar(20) DEFAULT NULL COMMENT 'Element''s xml:id attribute value',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Information on elements that are relevant for the text structure';

CREATE TABLE `<prefix>volume` (
  `number` int(11) unsigned NOT NULL COMMENT 'Number of the volume',
  `title` text NOT NULL COMMENT 'Title of the volume',
  `pagenum` int(11) NOT NULL COMMENT 'Pagenumber the volume starts at',
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Volumes and their titles';

CREATE TABLE `<prefix>xmlchunk` (
  `id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Chunk''s primary key',
  `page` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of the page this chunk is on',
  `column` varchar(10) NOT NULL DEFAULT '' COMMENT 'Column information',
  `prestack` text NOT NULL COMMENT 'Tags that are opened before this text chunk',
  `xml` text NOT NULL COMMENT 'The chunk''s XML source (not well-formed)',
  `poststack` text NOT NULL COMMENT 'Tags that have to be closed behind this text chunk',
  `plaintext` text NOT NULL COMMENT 'Chunk''s content as plaintext',
  `section` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Section ID',
  PRIMARY KEY (`id`),
  KEY `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Chunked XML document';
