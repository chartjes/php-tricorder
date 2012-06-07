![PHP-Tricorder logo](http://grumpy-testing.com/Tricorder_text.png)

(Logo generously created by [Jacques Woodcock](https://twitter.com/jacques_thekit))

PHP-Tricorder is a CLI utility that scans structure files created using [phpDocumentor](http://phpdoc.org)
and offers suggestions on potential problems with your code along with testing
strategy information.

Executing 'php tricorder.php' will give you some uage information.

Please read the file LICENSE included with this code for licensing details.

How To Use It
-------------

* Install [phpDocumentor](http://phpdoc.org) using your preferred method
* Use docblocks to describe the parameters and return types for your methods
* To generate a structure.xml document for an entire directory recursively, use 'phpdoc parse -d . -t'
* To generate a structure.xml document for a specific file, use 'phpdoc parse -f /path/to.file -t'
* Process the structure.xml document using tricorder: 'php tricorder.php /path/to/structure.xml'

Tricorder will then give you some suggestions on things to look for when testing
your classes. Here's some sample output:

	chartjes@php-vm:~/php-tricorder$ php tricorder.php ../building-testable-applications/lib/IBL/structure.xml 
	Reading in phpDocumentor structure file...

	FranchiseMapper.php

	Scanning FranchiseMapper

	__construct -- make sure to mock $conn as \PDO

	createFranchiseFromRow -- make sure to test $row using an empty array()

	delete -- make sure to mock $franchise as \IBL\Franchise

	findAll -- make sure to test method returns \IBL\Franchise instances
	findByConference -- make sure to test $conference using null or empty strings

	findByConferenceDivision -- make sure to test $conference using null or empty strings

	findByNickname -- make sure to test $nickname using null or empty strings

	findById -- make sure to test $id using non-integer values

	generateMap -- make sure to test $teamsTable using null or empty strings

	save -- make sure to mock $franchise as \IBL\Franchise

	_insert -- non-public methods are difficult to test in isolation
	_insert -- make sure to mock $franchise as \IBL\Franchise

	_update -- non-public methods are difficult to test in isolation
	_update -- make sure to mock $franchise as \IBL\Franchise
  

Feel free to hit me up on [Twitter](https://twitter.com/grmpyprogrammer) and pull requests
to make the tool better are always welcome. 
