TEIShredder Overview
=========================

What is it?
--------------
TEIShredder is a set of PHP classes for indexing TEI XML documents and retrieving information on the text structure (physical and logical), contained elements, named entites etc. The information extracted from the source document is saved in a relational database, i.e. it is a form of XML shredding – hence the name.

TEIShredder is based on code that was written for a scholarly project called “Sandrart.net” ([www.sandrart.net](http://www.sandrart.net), a cooperation project between Goethe-Universität Frankfurt am Main, Germany, and the Kunsthistorisches Institut, Florence, Italy, funded by the Deutsche Forschungsgemeinschaft [DFG]), but was modified to make it a stand-alone project/library. Meanwhile, the project’s original code has been dropped in favor of TEIShredder.

System requirements
-----------------

* PHP 5.3 with the standard extensions enabled
* Relational database; tested with MySQL, PostgreSQL and SQLite. CREATE statements for these three databases can be found in the “create-*.sql” files.


Using it
===========

Getting started
----------------
For a first quick test, open a shell on a Unix-oid system (Mac OS X, Linux, BSD, …), “cd” to the top TEIShredder directory and execute …

	sed 's/<prefix>//g' create-sqlite.sql | sqlite3 test.sqlite

…, which means: “Take the contents of ‘create-sqlite.sql’ in this directory, remove &lt;prefix&gt; from the tables’ names and create an empty SQLite database called ‘test.sqlite’ in this directory which contains these tables”.

Then, you can run “test.php”, which takes an input XML file from the “test” directory, indexes it and saves the result in that database. (If you are more familiar with MySQL or PostgreSQL or don’t have an sqlite3 executable at hand, you could of course also use MySQL/PgSQL by changing the PDO constructor in “test.php”.) Then, it displays information on the data that has been collected, for instance the number and titles of the volumes in the TEI document, occurrences of sections, named entities etc.

If you like, you can now inspect the database’s contents. For instance, you can view the elements that were indexed by executing ...

	sqlite3 test.sqlite 'SELECT * FROM element'

... at the shell.


TEI != TEI
------------
TEI can be used in many different ways. In my eyes, this is one of the very appealing features of TEI, but on the other hand, it makes developing generic tools much harder or impossible. TEIShredder is, to some extent, a generic tool insofar as it just processes TEI – but on the other hand, it has certain expectations of the TEI. Therefore, most likely, TEIShredder will not be able to process your unmodified TEI document, but it might be necessary to pre-process the document (for instance, using XSL-T or [XMLTransformer](https://github.com/BlueM/XMLTransformer)) to match these expectations.


Conventions / expectations
--------------------------

* If there are multiple volumes, each one must be enclosed by a a &lt;text&gt; block inside a &lt;group&gt; element.
* The main title of a volume is enclosed by a &lt;titlePart&gt; element. It is expected that each volume has a title, i.e. has a &lt;titlePart&gt; element.
* There must not be more than one &lt;titlePart&gt; element in each volume. If there are two or more, you should pre-process the XML and/or subclass the chunker class to make it ignore the unwanted &lt;titlePart&gt; elements when indexing.
* Text structure is encoded by nested &lt;div&gt; elements with &lt;head&gt; containing the section title.
* There is no special handling of columns, but only generic handling of &lt;milestone /&gt; elements. As the TEI Lite documentation suggests, columns should be encoded as &lt;milestone unit="column" [n="..."] /&gt;. Whenever TEIShredder encounters a &lt;milestone /&gt; element (regardless of whether it represents a column or some other change in a reference system), the values of @unit and @n (concatenated by "-", if both are present) will be saved together with the XML segment that follows this element.
* TEIShredder expects any element that should be indexed to have an @xml:id attribute, which means that elements without one will not be indexed. (Indexing such an element would be useless, as it could not be addressed, anyway.)


Database schema and performance
-------------------------------
The statements in the “create-*.sql” files are only a guess regarding what might work for you. For instance, if you have named entities in a TEI document, but the identifiers are strictly numeric, it might help to set the datatype of column “identifier” in the entitiy table to an integer. And, as usual, indexes are extremely important. If you find that a query runs longer than, let’s say, 20 or 30 milliseconds, you should take a close look at your database’s EXPLAIN output for the underlying SQL query. Also, you might want to add foreign key constraints (for example between page.volume and volume.number) if you think your application might benefit from it.

