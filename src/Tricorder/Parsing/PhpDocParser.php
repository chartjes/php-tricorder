<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Parsing;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Exception\InvalidArgumentException;
use Tricorder\Scanner\ClassScanner;

/**
 * Class PhpDocParser
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Parsing
 */
class PhpDocParser
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string          $basePath
     * @param OutputInterface $output
     */
    public function __construct($basePath, OutputInterface $output)
    {
        $this->output   = $output;
        $this->basePath = $basePath;
    }

    /**
     * Parse the $structureFile
     *
     * @param string $structureFile
     *
     * @throws \Tricorder\Exception\InvalidArgumentException
     */
    public function parse($structureFile)
    {
        if (!file_exists($structureFile)) {
            throw new InvalidArgumentException("Could not find phpDocumenter file [{$structureFile}]");
        }

        // Load in our structure and start iterating through it
        $this->output->writeln("Reading in phpDocumentor structure file...");

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
            $this->output->writeln($file['path']);
            $this->scanClasses($file->class);
            $filePath = join(DIRECTORY_SEPARATOR, array($this->basePath, $file['path']));
            $this->dependencyCheck($filePath);
        }
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
                    $this->output->writeln("{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes");
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
                $this->output->writeln("{$pathToFile} -- {$dependencyName} might need to be injected for testing purposes due to static method call");
            }
        }
    }

    /**
     * Scan our $classes to look for methods.
     *
     * @param SimpleXMLElement $classes
     */
    private function scanClasses(SimpleXMLElement $classes)
    {
        foreach ($classes as $class) {
            $classScanner = new ClassScanner($class, $this->output);
            $classScanner->scan($class);
        }
    }
}
