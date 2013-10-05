<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Scanner;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClassScanner
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Scanner
 */
class ClassScanner
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Scan the $class to find out if we have any data that we need to check for type.
     *
     * @param SimpleXMLElement $class
     */
    public function scan(SimpleXMLElement $class)
    {
        $this->output->writeln("Scanning " . $class->{'name'});
        $methodScanner = new MethodScanner($this->output);

        $methods = $class->method;
        foreach ($methods as $method) {
            $methodScanner->scan($method);
        }
    }
}
