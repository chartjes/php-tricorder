<?php
/**
 * This file is part of the tricorder package.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder;

/**
 * Class Application
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder
 */
class Application
{
    public function __construct($argv)
    {
        if (count($argv) == 0) {
            $this->showHelp();
        }

        $basePath = '.';
        for($argCounter = 0; $argCounter < count($argv); $argCounter++) {
            if ($argv[$argCounter] == '--help') {
                $this->showHelp();
            } else if (preg_match('/--path=(.*)/', $argv[$argCounter], $matches)) {
                $basePath = $matches[1];
            }
        }

        // Let's see if we have an actual file
        $structureFile = $argv[count($argv)-1];

        if (!file_exists($structureFile)) {
            echo "Could not find phpDocumenter file [{$structureFile}]" . PHP_EOL . PHP_EOL;
        }

        // Load in our structure and start iterating through it
        echo "Reading in phpDocumentor structure file..." . PHP_EOL . PHP_EOL;

        // I hate suppressing error messages, but we are trapping the results later
        $structureData = @simplexml_load_file($structureFile);

        if (!$structureData) {
            echo "{$structureFile} is not a properly formatted phpDocumentor structure";
            echo " file, please verify it's contents" . PHP_EOL;
            exit();
        }

        $files = $structureData->{'file'};

        if (!$files) {
            echo "Could not find proper file information in {$structureFile}" . PHP_EOL;
        }

        foreach ($files as $file) {
            echo $file['path'] . PHP_EOL . PHP_EOL;
            $this->scanClasses($file->class);
            $filePath = join(DIRECTORY_SEPARATOR, array($basePath, $file['path']));
            $this->dependencyCheck($filePath);
            echo "\n";
        }
    }

    private function showHelp()
    {
        echo "PHP-Tricoder - by Chris Hartjes" . PHP_EOL . PHP_EOL;
        echo "PHP-Tricoder analyzes phpDocumentor output to provide" . PHP_EOL;
        echo "suggestions on test scenarios and point out potential" . PHP_EOL;
        echo "problems" . PHP_EOL . PHP_EOL;
        echo "Usage: php tricoder.php [--help] [--path=</path/to/source>] </path/to/structure.xml>" . PHP_EOL . PHP_EOL;;
        exit();
    }

    /**
     * Read in our file, analyze the tokens and look for classes that might be
     * dependencies that need to be injected
     *
     * @param string $pathToFile
     */
    private function dependencyCheck($pathToFile)
    {
        $tokens = token_get_all(file_get_contents($pathToFile));
        $dependencyFlag = false;
        $depCount = 1;
        $dependencyName = '';

        foreach ($tokens as $idx => $token) {
            if ($dependencyFlag === true) {
                // If we encounter a opening (, then we know we have found
                // our dependency
                if (!is_string($token)) {
                    $dependencyName .= $token[1];
                } else {
                    $dependencyFlag = false;
                    $dependencyName = trim($dependencyName);
                    echo "{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes\n";
                }
            }

            if (is_long($token[0]) && $token[0] == T_NEW) {
                $dependencyFlag = true;
                $dependencyName = '';
            } elseif (is_long($token[0]) && $token[0] == T_DOUBLE_COLON) {
                $i = $idx;
                $dependencyName = '';

                while (!is_string($tokens[$i])) {
                    if (is_long($tokens[$i][0])
                        && $tokens[$i][0] !== T_DOUBLE_COLON
                        && $tokens[$i][0] !== ''
                    ) {
                        $dependencyName = $tokens[$i][1] . $dependencyName;
                    }

                    $i--;
                }

                $dependencyName = trim($dependencyName);
                echo "{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes due to static method call\n";
            }
        }
    }

    /**
     * Scan our classes to look for methods
     *
     * @param string $classXml
     */
    private function scanClasses($classXml)
    {
        foreach ($classXml as $classInfo) {
            echo "Scanning " . $classInfo->{'name'} . PHP_EOL . PHP_EOL;
            $this->scanMethods($classInfo->method);
        }
    }

    /**
     * Scan through our methods and find out if we have any parameters that we
     * need to check for type
     *
     * @param SimpleXMLElement $methods
     */
    private function scanMethods($methods)
    {
        foreach ($methods as $method) {
            $methodHasSuggestions = $this->isVisible(
                (string)$method->name,
                (string)$method['visibility']
            );

            // Convert our tag information into an array for easy manipulation
            $methodTags = array();
            foreach ($method->docblock->tag as $tag) {
                array_push($methodTags, json_decode(json_encode((array)$tag), 1));
            }

            $tricorderTags = array_filter($methodTags, function($tag) {
                    if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'tricorder') {
                        return true;
                    }
                });

            // Check to see if we have any parameters that we need to test
            $paramTags = array_filter($methodTags, function($tag) {
                    if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'param') {
                        return true;
                    }
                });

            $argsHaveSuggestions = $this->scanArguments(
                (string)$method->name,
                $paramTags,
                $tricorderTags
            );

            // Grab our method return information
            $returnTag = array_filter($methodTags, function($tag) {
                    if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'return') {
                        return true;
                    }
                });

            $returnTypeHasSuggestions = $this->processReturnType(
                (string)$method->name,
                $returnTag,
                $tricorderTags
            );

            echo ($methodHasSuggestions == false
                && $argsHaveSuggestions == false
                && $returnTypeHasSuggestions == false)
                ? ''
                : PHP_EOL;
        }
    }

    /**
     * Determine if the method passed in is publicly visible
     *
     * @param string $methodName
     * @param string $visibility
     *
     * @return bool Whether the method is public
     */
    private function isVisible($methodName, $visibility)
    {
        $methodIsVisible = false;

        // If a method is protected, flag it as hard-to-test
        if ($visibility !== 'public') {
            $methodIsVisible = true;
            echo "{$methodName} -- non-public methods are difficult to test in isolation" . PHP_EOL;
        }

        return $methodIsVisible;
    }

    /**
     * Iterate through our list of arguments for the method, examining the tags
     * to see what the types are
     *
     * @param string $methodName
     * @param array  $tags
     * @param array  $tricorderTags
     *
     * @return boolean
     */
    private function scanArguments($methodName, $tags, $tricorderTags)
    {
        $argumentsHaveSuggestions = array();

        foreach ($tags as $tag) {
            $argumentsHaveSuggestions[] = $this->processArgumentType($methodName, $tag, $tricorderTags);
        }

        return in_array(true, $argumentsHaveSuggestions);
    }

    /**
     * Look at the argument type and react accordingly
     *
     * @param string $methodName
     * @param array  $tag
     * @param array  $tricorderTags
     *
     * @return boolean
     */
    private function processArgumentType($methodName, $tag, $tricorderTags)
    {
        // @todo Variable not used in scope
        $acceptedTypes = array(
            'array',
            'string',
            'integer'
        );

        $argHasSuggestions = false;
        $varName = $tag['@attributes']['variable'] ?: null;
        $tagType = $tag['type'];

        $coverage = array();
        foreach ($tricorderTags as $tag) {
            if (isset($tag['@attributes']['description']) && preg_match('/^coversMethodAccepts(.*?)Values\b/', $tag['@attributes']['description'], $matches)) {
                array_push($coverage, strtolower($matches[1]));
            }
        }

        /**
         * Sometimes people send us param types like bool|string, so we need to
         * search for those and convert them to 'mixed'
         */
        if (stristr('|', $tagType) === true) {
            $tagType = 'mixed';
        }

        switch ($tagType) {
            case 'array':
                if (in_array('array', $coverage)) return false;
                $msg = "test {$varName} using an empty array()";
                $argHasSuggestions = true;
                break;
            case 'bool':
            case 'boolean':
                if (in_array('bool', $coverage) || in_array('boolean', $coverage)) return false;
                $msg = "test {$varName} using both true and false";
                $argHasSuggestions = true;
                break;
            case 'int':
            case 'integer':
                if (in_array('int', $coverage) || in_array('integer', $coverage)) return false;
                $msg = "test {$varName} using non-integer values";
                $argHasSuggestions = true;
                break;
            case 'mixed':
                $msg = "test {$varName} using all potential values";
                $argHasSuggestions = true;
                break;
            case 'string':
                if (in_array('string', $coverage)) return false;
                $msg = "test {$varName} using null or empty strings";
                $argHasSuggestions = true;
                break;
            case 'object':
            default:
                $msg = "mock {$varName} as {$tagType}";
                $argHasSuggestions = true;
                break;
        }

        echo "{$methodName} -- {$msg}" . PHP_EOL;

        return $argHasSuggestions;
    }

    /**
     * Look at the return type and react accordingly
     *
     * @param string $methodName
     * @param array  $tag
     * @param array  $tricorderTags
     *
     * @return boolean
     */
    private function processReturnType($methodName, $tag, $tricorderTags)
    {
        // Flatten the array a bit so we can check for attributes
        $tagInfo = array_shift($tag);

        if ($tagInfo == null) {
            return false;
        }

        $tagType = $tagInfo['type'];
        $coverage = array();

        foreach ($tricorderTags as $tag) {
            if (isset($tag['@attributes']['description']) && preg_match('/^coversMethodReturns(.*?)Values\b/', $tag['@attributes']['description'], $matches)) {
                array_push($coverage, strtolower($matches[1]));
            }
        }

        /**
         * Sometimes people send us return types like bool|string, so we need to
         * search for those and convert them to 'mixed'
         */
        if (stristr('|', $tagType) === true) {
            $tagType = 'mixed';
        }

        // Make sure to not bother with this if we already ran into this
        if (in_array($tagType, $coverage)) {
            return false;
        }

        switch ($tagType) {
            case 'mixed':
                $msg = "test method returns all potential values";
                break;
            case 'bool':
            case 'boolean':
                $msg = "test method returns boolean values";
                break;
            case 'int':
            case 'integer':
                $msg = "test method returns non-integer values";
                break;
            case 'string':
                $msg = "test method returns expected string values";
                break;
            default:
                $msg = "test method returns {$tagType} instances";
                break;
        }

        echo "{$methodName} -- {$msg}" . PHP_EOL;
    }
}
