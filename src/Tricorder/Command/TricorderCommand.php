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

namespace Tricorder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Exception\InvalidArgumentException;

/**
 * Class TricorderCommand
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder
 */
class TricorderCommand extends Command
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    public function configure()
    {
        $help = <<<HELP
PHP-Tricorder - by Chris Hartjes
PHP-Tricorder analyzes phpDocumentor output to provide
suggestions on test scenarios and point out potential problems
HELP;

        $this->setName('tricorder');
        $this->setDescription('PHP-Tricorder - by Chris Hartjes');
        $this->setHelp($help);
        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'The xml structure file'
        );
        $this->addOption(
            'path',
            null,
            InputOption::VALUE_OPTIONAL,
            'The path where to find the classes'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $basePath = $input->getOption('path');

        // Let's see if we have an actual file
        $structureFile = $input->getArgument('file');

        if (!file_exists($structureFile)) {
            throw new InvalidArgumentException("Could not find phpDocumenter file [{$structureFile}]");
        }

        // Load in our structure and start iterating through it
        $this->outputMessage("Reading in phpDocumentor structure file...");

        // I hate suppressing error messages, but we are trapping the results later
        $structureData = @simplexml_load_file($structureFile);

        if (!$structureData) {
            throw new InvalidArgumentException("{$structureFile} is not a properly formatted phpDocumentor structure file, please verify it's contents");
        }

        $files = $structureData->{'file'};

        if (!$files) {
            throw new InvalidArgumentException("Could not find proper file information in {$structureFile}");
        }

        foreach ($files as $file) {
            $this->outputMessage($file['path']);
            $this->scanClasses($file->class);
            $filePath = join(DIRECTORY_SEPARATOR, array($basePath, $file['path']));
            $this->dependencyCheck($filePath);
        }
    }

    /**
     * Outputs a message
     *
     * @param $message The message to output
     */
    private function outputMessage($message)
    {
        $this->output->writeln($message);
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
                    $this->outputMessage("{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes");
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
                $this->outputMessage("{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes due to static method call");
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
            $this->outputMessage("Scanning " . $classInfo->{'name'});
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

            if ($methodHasSuggestions || $argsHaveSuggestions || $returnTypeHasSuggestions) {
                $this->outputMessage('');
            }
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
            $this->outputMessage("{$methodName} -- non-public methods are difficult to test in isolation");
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
                if (in_array('array', $coverage)) {
                    return false;
                }
                $msg = "test {$varName} using an empty array()";
                break;
            case 'bool':
            case 'boolean':
                if (in_array('bool', $coverage) || in_array('boolean', $coverage)) {
                    return false;
                }
                $msg = "test {$varName} using both true and false";
                break;
            case 'int':
            case 'integer':
                if (in_array('int', $coverage) || in_array('integer', $coverage)) {
                    return false;
                }
                $msg = "test {$varName} using non-integer values";
                break;
            case 'mixed':
                $msg = "test {$varName} using all potential values";
                break;
            case 'string':
                if (in_array('string', $coverage)) {
                    return false;
                }
                $msg = "test {$varName} using null or empty strings";
                break;
            case 'object':
            default:
                $msg = "mock {$varName} as {$tagType}";
                break;
        }

        $this->outputMessage("{$methodName} -- {$msg}");

        return true;
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

        $this->outputMessage("{$methodName} -- {$msg}");
    }
}
