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
use Tricorder\Parsing\PhpDocParser;

/**
 * Class TricorderCommand
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder
 */
class TricorderCommand extends Command
{
    public function configure()
    {
        $help = <<<HELP
PHP-Tricorder - by Chris Hartjes
Scans files and phpDocumentor output to provide suggestions on
test scenarios and point out potential problems

Sample Usage:

tricorder --file=/path/to/single/file
tricorder --phpdox=/path/to/structurefile
tricorder --phpdox=/path/to/structurefile --path=/path/to/multiple/files
tricorder --path=/path/to/multiple/files

HELP;

        $this->setName('tricorder');
        $this->setDescription('PHP-Tricorder - by Chris Hartjes');
        $this->setHelp($help);
        $this->addOption(
            'file',
            null,
            InputArgument::OPTIONAL,
            'File containing class to scan'
        );
        $this->addOption(
            'phpdox',
            null,
            InputOption::VALUE_OPTIONAL,
            'The phpDocumenter XML output for the class you wish to test'
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
        $basePath      = $input->getOption('path');
        $structureFile = $input->getOption('phpdox');
        $file          = $input->getOption('file');
        $messages      = array();

        if (!(($basePath && $structureFile) || ($file && (!$basePath || !$file)) && ($basePath || $structureFile || $file))) {
            echo $this->getHelp();
            return;
        }

        if (!empty($basePath)) {
            $parser = new PhpDocParser($basePath, $output);
            $parser->parse($structureFile);
        }

        if (!empty($file) && file_exists($file)) {
            $attributeScanner = new \Tricorder\Scanner\AttributeScanner($file);
            $attributeMessages = $attributeScanner->scan();
            $messages = array_merge($attributeMessages, $messages);
        }

        foreach ($messages as $message) {
            $output->writeln($message);
        }
    }
}
