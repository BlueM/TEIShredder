TEIShredder Overview
=========================

What is it?
--------------
TEIShredder is a set of PHP classes for indexing TEI XML documents and retrieving specific information later. The information extracted from the source document is saved in a relational database, i.e. it is a form of XML shredding – hence the name.

Why was it developed?
--------------------
TEIShredder was developed in the course of a scholarly project called “Sandrart.net” by the Goethe-Universität Frankfurt am Main (Germany) and the Kunsthistorisches Institut in Florenz (Italy).

System requirements
-----------------
PHP 5.3
Relational database; tested with MySQL and SQLite, but as it uses PHP’s PDO extension, other supported databases may be usable.

Using it
===========

TEI != TEI
----------
TEI can be used in many different ways. In my eyes, this is one of the very appealing features of TEI, but on the other hand, it makes developing generic tools much harder or impossible. TEIShredder is, to some extent, a generic tool insofar as it just processes TEI – but on the other hand, it has certain expectations of the TEI. You may find that a TEI document you wish to process does not meet TEIShredder’s expectations, and for cases like these, I suggest pre-processing the TEI in a way that will result in a processable document.

Customizing conversion by defining callbacks
---------------------------------------------
Default implementation. Optional callbacks.


Conventions / expectations
===========================

* If there are multiple volumes, each one must be enclosed by a a <text> block inside a <group> element.
* The main title of a volume is enclosed by a <titlePart> element that has either no @type attribute or the @type attribute’s value is “main”.
* There must not be more than one <titlePart> element in each volume that fulfills to the abovementioned conditions.
* Text structure is encoded by nested <div> elements with <head> containing the section title.