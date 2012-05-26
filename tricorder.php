#!/usr/bin/env php
<?php
/**
 * PHP-Tricorder
 *
 * A CLI utility that will scan a structure file created using
 * phpDocumentor and give you some suggestions on how to test
 * the classes and methods present in the structure file
 *
 * @author Chris Hartjes
 * @version 0.1
 */
array_shift($argv);

if (count($argv) == 0 || $argv[0] == '--help') {
    echo "PHP-Tricoder - by Chris Hartjes" . PHP_EOL . PHP_EOL;
    echo "PHP-Tricoder analyzes phpDocumentor output to provide" . PHP_EOL;
    echo "suggestions on test scenarios and point out potential" . PHP_EOL;
    echo "problems" . PHP_EOL . PHP_EOL;
    ECHO "Usage: tricoder.php </path/to/structure.xml>" . PHP_EOL . PHP_EOL;;
    exit();
}

// Let's see if we have an actual file
$structureFile = $argv[0];

if (!file_exists($structureFile)) {
    echo "Could not find phpDocumenter file [{$structureFile}]" . PHP_EOL . PHP_EOL;
}

// Load in our structure and start iterating through it
echo "Reading in phpDocumentor structure file..." . PHP_EOL . PHP_EOL;
$structureData = simplexml_load_file($structureFile);
$files = $structureData->{'file'};

foreach ($files as $file) {
    echo $file['path'] . PHP_EOL . PHP_EOL;
    scanClasses($file->class);
}

/**
 * Scan our classes to look for methods
 *
 * @param string $classXml
 */
function scanClasses($classXml) {
    foreach ($classXml as $classInfo) {
        echo "Scanning " . $classInfo->{'name'} . PHP_EOL;
        scanMethods($classInfo->method);
    }
}

/**
 * Scan through our methods and find out if we have any parameters that we
 * need to check for type
 *
 * @param SimpleXMLElement $methods
 */
function scanMethods($methods) {
    foreach ($methods as $method) {
        isVisibile((string)$method->name, (string)$method['visibility']);

        // Check to see if we have any parameters that we need to test
        $paramTags = array_filter((array)$method->docblock->tag, function($tag) {
            if (isset($tag['name']) && $tag['name'] == 'param') {
                return true;
            }
        });

        scanArguments((string)$method->name, $paramTags);
    }
}

/**
 * Determine if the method passed in is publically visible
 *
 * @param string $methodName
 * @param string $visibility
 */
function isVisibile($methodName, $visibility) {
    // If a method is protected, flag it as hard-to-test
    if ($visibility !== 'public') {
        echo "{$methodName} -- non-public methods are difficult to test in isolation" . PHP_EOL;
    }
}

/**
 * Iterate through our list of arguments for the method, examining the tags
 * to see what the types are
 *
 * @param string $methodName
 * @param array $tags
 */
function scanArguments($methodName, $tags) {
    foreach ($tags as $tag) {
        processArgumentType($methodName, $tag);
    }
}

/**
 * Look at the argument type and react accordingly
 *
 * @param string $methodName
 * @param array $tag
 */
function processArgumentType($methodName, $tag) {
    $acceptedTypes = array(
        'array',
        'string', 
        'integer'
    );
    if (!in_array($tag['type'], $acceptedTypes)) {
        echo "{$methodName} -- make sure to mock "
            . $tag['variable'] 
            . " as " 
            . $tag['type'] 
            . PHP_EOL;
    }
}

