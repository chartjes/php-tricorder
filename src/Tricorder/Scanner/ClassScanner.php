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
class ClassScanner implements Scanner
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var SimpleXMLElement
     */
    private $class;

    /**
     * @param SimpleXMLElement $class
     * @param OutputInterface  $output
     */
    public function __construct(SimpleXMLElement $class, OutputInterface $output)
    {
        $this->output = $output;
        $this->class  = $class;
    }

    /**
     * Scan the $class to find out if we have any data that we need to check for type.
     */
    public function scan()
    {
        $this->output->writeln("Scanning " . $this->class->{'name'});

        $methods = $this->class->method;
        foreach ($methods as $method) {
            $methodScanner = new MethodScanner($method, $this->output);
            $methodScanner->scan($method);
        }
    }
}
