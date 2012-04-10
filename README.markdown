TEIShredder Overview
=========================

What is it?
--------------
TEIShredder is a set of PHP classes for indexing TEI XML documents and retrieving specific information later. The information extracted from the source document is saved in a relational database, i.e. it is a form of XML shredding – hence the name.

System requirements
-----------------

* PHP 5.3
* Relational database; tested with MySQL and SQLite, but as it uses PHP’s PDO extension, other supported databases may be usable. CREATE-Statements for these two databases are in files “create-mysql.sql” and “create-sqlite.sql” respectively

Documentation
-------------
There is no end-user documentation yet, but the code is fully documented with PHPDoc-style doc comments.

Status of the project
---------------------
Part of TEIShredder is production-ready, but at the moment some parts are rather fragmentary, as it was forked from a larger project. Please keep that in mind when considering to use it.

Using it
===========

TEI != TEI
----------
TEI can be used in many different ways. In my eyes, this is one of the very appealing features of TEI, but on the other hand, it makes developing generic tools much harder or impossible. TEIShredder is, to some extent, a generic tool insofar as it just processes TEI – but on the other hand, it has certain expectations of the TEI. You may find that a TEI document you wish to process does not meet TEIShredder’s expectations, and for cases like these, I suggest pre-processing the TEI in a way that will result in a processable document.


Exluding sections from being indexed
------------------------------------
By default, TEIShredder (currently, this might be subject to change) collects information of a text’s structure based on &lt;div&gt;, &lt;text&gt;, &lt;titlePage&gt; and &lt;front&gt; tags. If you want any of these not included, you can add a @noindex attribute with value of 1. As, of course, @noindex is not a valid TEI attribute, you should transform the source XML document before passing it to TEIShredder, for instance using XSL-T.

Conventions / expectations
--------------------------

* If there are multiple volumes, each one must be enclosed by a a &lt;text&gt; block inside a &lt;group&gt; element.
* The main title of a volume is enclosed by a &lt;titlePart&gt; element that has either no @type attribute or the @type attribute’s value is “main”. Additionally, @noindex must not be set to 1.
* There must not be more than one &lt;titlePart&gt; element in each volume that fulfills to the abovementioned conditions.
* Text structure is encoded by nested &lt;div&gt; elements with &lt;head&gt; containing the section title.
