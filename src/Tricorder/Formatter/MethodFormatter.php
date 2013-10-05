<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MethodFormatter
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formatter
 */
class MethodFormatter implements Formatter
{
    /**
     * @var SimpleXMLElement
     */
    private $method;

    /**
     * @param SimpleXMLElement $method
     */
    public function __construct(SimpleXMLElement $method)
    {
        $this->method = $method;
    }

    /**
     * Output the message for methods.
     *
     * @param OutputInterface  $output
     *
     * @return bool Whether the method is public
     */
    public function outputMessage(OutputInterface $output)
    {
        // If a method is protected, flag it as hard-to-test
        if ($this->method->visibility !== 'public') {
            $output->writeln("{$this->method->name} -- non-public methods are difficult to test in isolation");
        }
    }
}
