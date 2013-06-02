![PHP-Tricorder logo](http://grumpy-testing.com/Tricorder_text.png)

(Logo generously created by [Jacques Woodcock](https://twitter.com/jacques_thekit))

PHP-Tricorder is a CLI utility that scans structure files created using [phpDocumentor](http://phpdoc.org)
and offers suggestions on potential problems with your code along with testing
strategy information.

Executing `/path/to/php tricorder.php` will give you some usage information.

Please read the file LICENSE included with this code for licensing details.

How To Use It
-------------

* Install [phpDocumentor](http://phpdoc.org) using your preferred method
* Use docblocks to describe the parameters and return types for your methods
* To generate a structure.xml document for an entire directory recursively, use `phpdoc parse -d . -t`
* To generate a structure.xml document for a specific file, use `phpdoc parse -f /path/to.file -t`
* Install the dependencies using [Composer](http://getcomposer.org/) `/path/to/php composer.phar install`
* Process the structure.xml document using tricorder: `/path/to/php tricorder.php /path/to/structure.xml`

Tricorder will then give you some suggestions on things to look for when testing
your classes. Here's some sample output:

	chartjes@php-vm:~/php-tricorder$ php tricorder.php ../building-testable-applications/lib/IBL/structure.xml 
	Reading in phpDocumentor structure file...

	FranchiseMapper.php

	Scanning FranchiseMapper

	__construct -- mock $conn as \PDO

	createFranchiseFromRow -- test $row using an empty array()

	delete -- mock $franchise as \IBL\Franchise

	findAll -- test method returns \IBL\Franchise instances
	findByConference -- test $conference using null or empty strings

	findByConferenceDivision -- test $conference using null or empty strings

	findByNickname -- test $nickname using null or empty strings

	findById -- test $id using non-integer values

	generateMap -- test $teamsTable using null or empty strings

	save -- mock $franchise as \IBL\Franchise

	_insert -- non-public methods are difficult to test in isolation
	_insert -- mock $franchise as \IBL\Franchise

	_update -- non-public methods are difficult to test in isolation
	_update -- mock $franchise as \IBL\Franchise

	\IBL\Franchise might need to be injected for testing purposes
	\Exception might need to be injected for testing purposes
	\Exception might need to be injected for testing purposes
	\Exception might need to be injected for testing purposes
	\Exception might need to be injected for testing purposes
	\Exception might need to be injected for testing purposes

  

Feel free to hit me up on [Twitter](https://twitter.com/grmpyprogrammer) and pull requests
to make the tool better are always welcome. 
