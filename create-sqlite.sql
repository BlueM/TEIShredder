-- Please replace “<prefix>” with the desired prefix or remove it

CREATE TABLE "<prefix>element" (
	"xmlid" TEXT PRIMARY KEY,
	"element" TEXT NOT NULL,
	"page" INTEGER NOT NULL,
	"chunk" INTEGER NOT NULL,
	"attrn" TEXT,
	"attrtargetend" TEXT,
	"data" TEXT
);


CREATE TABLE "<prefix>notation" (
	"xmlid" TEXT,
	"page" INTEGER NOT NULL,
	"domain" TEXT NOT NULL,
	"object" INTEGER,
	"notation" TEXT,
	"context" TEXT,
	"container" TEXT,
	"containerid" TEXT,
	"chunk" INTEGER,
	"notationhash" TEXT
);

CREATE INDEX "domain-object" ON "<prefix>notation" ("domain","object");

CREATE TABLE "<prefix>page" (
	"page" INTEGER PRIMARY KEY,
	"xmlid" TEXT UNIQUE,
	"volume" INTEGER NOT NULL,
	"plaintext" TEXT,
	"n" TEXT,
	"rend" TEXT,
	"facs" TEXT
);


CREATE TABLE "<prefix>structure" (
	"id" INTEGER PRIMARY KEY,
	"volume" INTEGER,
	"title" TEXT,
	"page" INTEGER NOT NULL,
	"level" INTEGER NOT NULL,
	"type" TEXT NOT NULL,
	"xmlid" TEXT
);


CREATE TABLE "<prefix>volume" (
	  "number" INTEGER NOT NULL,
	  "title" text NOT NULL,
	  "pagenum" INTEGER NOT NULL,
	  PRIMARY KEY ("number")
);


CREATE TABLE "<prefix>xmlchunk" (
	"id" INTEGER PRIMARY KEY ASC,
	"volume" INTEGER NOT NULL,
	"page" INTEGER NOT NULL,
	"col" TEXT,
	"prestack" TEXT,
	"xml" TEXT,
	"poststack" TEXT,
	"plaintext" TEXT,
	"section" INTEGER NOT NULL
);
