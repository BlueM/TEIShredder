-- Please replace “<prefix>” with the desired prefix or remove it

CREATE TABLE "<prefix>element" (
	"xmlid" TEXT PRIMARY KEY,
	"element" TEXT NOT NULL,
	"page" INTEGER NOT NULL,
	"chunk" INTEGER NOT NULL
);

CREATE TABLE "<prefix>entity" (
	"xmlid" TEXT,
	"page" INTEGER NOT NULL,
	"domain" TEXT NOT NULL,
	"identifier" TEXT NOT NULL,
	"contextstart" TEXT,
	"notation" TEXT,
	"contextend" TEXT,
	"chunk" INTEGER,
	"notationhash" TEXT
);

CREATE INDEX "domain_identifier" ON "<prefix>entity" ("domain","identifier");

CREATE TABLE "<prefix>page" (
	"number" INTEGER PRIMARY KEY,
	"xmlid" TEXT UNIQUE,
	"volume" INTEGER NOT NULL,
	"plaintext" TEXT,
	"n" TEXT,
	"rend" TEXT
);

CREATE TABLE "<prefix>section" (
	"id" INTEGER PRIMARY KEY,
	"volume" INTEGER,
	"title" TEXT,
	"page" INTEGER NOT NULL,
	"level" INTEGER NOT NULL,
	"element" TEXT NOT NULL,
	"xmlid" TEXT
);

CREATE TABLE "<prefix>volume" (
	  "number" INTEGER NOT NULL,
	  "title" text NOT NULL,
	  "pagenumber" INTEGER NOT NULL,
	  PRIMARY KEY ("number")
);

CREATE TABLE "<prefix>xmlchunk" (
	"id" INTEGER PRIMARY KEY ASC,
	"page" INTEGER NOT NULL,
	"milestone" TEXT,
	"prestack" TEXT,
	"xml" TEXT,
	"poststack" TEXT,
	"plaintext" TEXT,
	"section" INTEGER NOT NULL
);
