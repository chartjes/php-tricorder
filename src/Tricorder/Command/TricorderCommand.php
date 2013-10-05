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
        $basePath      = $input->getOption('path');
        $structureFile = $input->getArgument('file');

        $parser = new PhpDocParser($basePath, $output);
        $parser->parse($structureFile);
    }
}
