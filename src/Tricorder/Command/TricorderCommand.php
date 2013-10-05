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

use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Exception\InvalidArgumentException;
use Tricorder\Formatter\MethodFormatter;
use Tricorder\Processor\ArgumentTypeProcessor;
use Tricorder\Processor\ReturnTypeProcessor;
use Tricorder\Tag\Extractor\MethodTagExtractor;
use Tricorder\Tag\Extractor\TricorderTagExtractor;

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
     * @param SimpleXMLElement $classXml
     */
    private function scanClasses(SimpleXMLElement $classXml)
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
    private function scanMethods(SimpleXMLElement $methods)
    {
        $tricorderTagExtractor = new MethodTagExtractor();
        foreach ($methods as $method) {
            $methodTags = $tricorderTagExtractor->extractTags($method);

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

            // Grab our method return information
            $returnTag = array_filter($methodTags, function($tag) {
                    if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'return') {
                        return true;
                    }
                });

            $this->scanArguments((string)$method->name, $paramTags, $tricorderTags);

            // Process ReturnType
            $processor = new ReturnTypeProcessor($this->output);
            $processor->process((string)$method->name, $returnTag, $tricorderTags);

            $methodFormatter = new MethodFormatter($method);
            $methodFormatter->outputMessage($this->output);
        }
    }

    /**
     * Iterate through our list of arguments for the method, examining the tags
     * to see what the types are
     *
     * @param string $methodName
     * @param array  $tags
     * @param array  $tricorderTags
     */
    private function scanArguments($methodName, array $tags, array $tricorderTags)
    {
        $processor = new ArgumentTypeProcessor($this->output);
        foreach ($tags as $tag) {
            $processor->process($methodName, $tag, $tricorderTags);
        }
    }
}
