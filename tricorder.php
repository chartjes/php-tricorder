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
 * @author Yannick Voyer
 * @version 0.1
 */

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Tricorder\Command\TricorderCommand;

// Restructure the arguments to force the tricorder command by default
unset($argv[0]);
$argv = array_merge(array('I am getting ignored, I should stay there', 'tricorder'), $argv);

$application = new Application('tricorder', '0.1');
$application->add(new TricorderCommand());
$application->run(new ArgvInput($argv));