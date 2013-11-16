-- Please replace “<prefix>” with the desired prefix or remove it

CREATE TABLE "<prefix>element" (
  "xmlid" character varying(24) NOT NULL,
  "element" character varying(20) NOT NULL DEFAULT ''::character varying,
  "page" integer NOT NULL DEFAULT 0,
  "chunk" integer NOT NULL DEFAULT 0,
  CONSTRAINT "<prefix>element_pkey" PRIMARY KEY ("xmlid"),
  CONSTRAINT "<prefix>element_page_check" CHECK ("page" > 0),
  CONSTRAINT "<prefix>element_page_check1" CHECK ("page" > 0)
);

CREATE INDEX "element" ON "<prefix>element" USING btree ("element");

COMMENT ON COLUMN "<prefix>element"."xmlid"   IS 'Unique xml:id attribute of element';
COMMENT ON COLUMN "<prefix>element"."element" IS 'Name of XML element';
COMMENT ON COLUMN "<prefix>element"."page"    IS 'Number of the page which contains this element';
COMMENT ON COLUMN "<prefix>element"."chunk"   IS 'ID of chunk this element belongs to';

CREATE TABLE "<prefix>entity" (
  "xmlid" character varying(20) DEFAULT ''::character varying,
  "page" integer NOT NULL,
  "domain" character varying(20) NOT NULL DEFAULT ''::character varying,
  "identifier" character varying(50) DEFAULT ''::character varying,
  "contextstart" text,
  "notation" text NOT NULL,
  "contextend" text,
  "chunk" integer,
  "notationhash" character varying(16) NOT NULL DEFAULT ''::character varying
);

CREATE INDEX "domain_identifier" ON "<prefix>entity" USING btree ("domain", "identifier");
CREATE INDEX "page" ON "<prefix>entity" USING btree ("page");

COMMENT ON COLUMN "<prefix>entity"."xmlid"        IS 'xml:id attr. of elemnt containing the object occurrence';
COMMENT ON COLUMN "<prefix>entity"."page"         IS 'Page number, 1-based';
COMMENT ON COLUMN "<prefix>entity"."domain"       IS 'Object domain';
COMMENT ON COLUMN "<prefix>entity"."identifier"   IS 'Object identifier';
COMMENT ON COLUMN "<prefix>entity"."contextstart" IS 'Context before the notation';
COMMENT ON COLUMN "<prefix>entity"."notation"     IS 'Exact notation/wording used in the text';
COMMENT ON COLUMN "<prefix>entity"."contextend"   IS 'Context after the notation';
COMMENT ON COLUMN "<prefix>entity"."chunk"        IS 'ID of the chunk';
COMMENT ON COLUMN "<prefix>entity"."notationhash" IS 'Truncated MD5 hash of the notation';


CREATE TABLE "<prefix>page" (
  "number" integer NOT NULL DEFAULT 0,
  "xmlid" character varying(40) NOT NULL DEFAULT ''::character varying,
  "volume" integer NOT NULL DEFAULT 0,
  "plaintext" text NOT NULL,
  "n" character varying(100) DEFAULT ''::character varying,
  "rend" character varying(100) DEFAULT ''::character varying,
  CONSTRAINT "<prefix>page_pkey" PRIMARY KEY ("number")
);

CREATE UNIQUE INDEX "xmlid" ON "<prefix>page" USING btree ("xmlid");

COMMENT ON COLUMN "<prefix>page"."number"    IS 'Page number';
COMMENT ON COLUMN "<prefix>page"."xmlid"     IS 'xml:id of <pb/> element';
COMMENT ON COLUMN "<prefix>page"."volume"    IS 'Volume number';
COMMENT ON COLUMN "<prefix>page"."plaintext" IS 'Page contents as plaintext';
COMMENT ON COLUMN "<prefix>page"."n"         IS 'Value of @n attribute';
COMMENT ON COLUMN "<prefix>page"."rend"      IS 'Value of @rend attribute';


CREATE TABLE "<prefix>section"
(
  "id" integer NOT NULL DEFAULT 0,
  "volume" integer NOT NULL,
  "title" text NOT NULL,
  "page" integer NOT NULL,
  "level" integer DEFAULT 0,
  "element" character varying(20) DEFAULT NULL::character varying,
  "xmlid" character varying(20) DEFAULT NULL::character varying,
  CONSTRAINT "<prefix>section_pkey" PRIMARY KEY ("id")
);

COMMENT ON COLUMN "<prefix>section"."id"      IS 'Unique strucuture element ID/PK';
COMMENT ON COLUMN "<prefix>section"."volume"  IS 'Volume number, 1-based';
COMMENT ON COLUMN "<prefix>section"."title"   IS 'Title of the section';
COMMENT ON COLUMN "<prefix>section"."page"    IS 'Page number (of digitized page, not source text)';
COMMENT ON COLUMN "<prefix>section"."level"   IS 'Element''s text structure level';
COMMENT ON COLUMN "<prefix>section"."element" IS 'Element/tag name';
COMMENT ON COLUMN "<prefix>section"."xmlid"   IS 'Element''s xml:id attribute value';


CREATE TABLE "<prefix>volume"
(
  "number" integer NOT NULL,
  "title" text NOT NULL,
  "pagenumber" integer NOT NULL,
  CONSTRAINT "<prefix>volume_pkey" PRIMARY KEY ("number")
);

COMMENT ON COLUMN "<prefix>volume"."number"     IS 'Volume number, 1-based';
COMMENT ON COLUMN "<prefix>volume"."title"      IS 'Volume title';
COMMENT ON COLUMN "<prefix>volume"."pagenumber" IS 'Pagenumber the volume starts at';


CREATE TABLE "<prefix>xmlchunk"
(
  "id" integer NOT NULL DEFAULT 0,
  "page" integer NOT NULL DEFAULT 0,
  "milestone" character varying(40) DEFAULT ''::character varying,
  "prestack" text NOT NULL,
  "xml" text NOT NULL,
  "poststack" text NOT NULL,
  "plaintext" text NOT NULL,
  "section" integer NOT NULL DEFAULT 0,
  CONSTRAINT "<prefix>xmlchunk_pkey" PRIMARY KEY ("id")
);

COMMENT ON COLUMN "<prefix>xmlchunk"."id"        IS 'Chunk''s primary key';
COMMENT ON COLUMN "<prefix>xmlchunk"."page"      IS 'Number of the page this chunk is on';
COMMENT ON COLUMN "<prefix>xmlchunk"."milestone" IS 'Current <milestone /> context';
COMMENT ON COLUMN "<prefix>xmlchunk"."prestack"  IS 'Tags that are opened before this text chunk';
COMMENT ON COLUMN "<prefix>xmlchunk"."xml"       IS 'The chunk''s XML source (not well-formed)';
COMMENT ON COLUMN "<prefix>xmlchunk"."poststack" IS 'Tags that have to be closed behind this text chunk';
COMMENT ON COLUMN "<prefix>xmlchunk"."plaintext" IS 'Chunk''s content as plaintext';
COMMENT ON COLUMN "<prefix>xmlchunk"."section"   IS 'Section ID';
