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

