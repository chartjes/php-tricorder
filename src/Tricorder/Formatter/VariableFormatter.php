<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VariableFormatter
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formater
 */
class VariableFormatter implements Formatter
{
    /**
     * @var string
     */
    private $tagType;

    /**
     * @var string
     */
    private $varName;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param string $tagType
     * @param string $varName
     * @param string $methodName
     */
    public function __construct($tagType, $varName, $methodName)
    {
        $this->tagType    = $tagType;
        $this->varName    = $varName;
        $this->methodName = $methodName;
    }

    /**
     * Output the suggestion.
     *
     * @param OutputInterface  $output
     */
    public function outputMessage(OutputInterface $output)
    {
        switch ($this->tagType) {
            case 'array':
                $msg = "test {$this->varName} using an empty array()";
                break;
            case 'bool':
            case 'boolean':
                $msg = "test {$this->varName} using both true and false";
                break;
            case 'int':
            case 'integer':
                $msg = "test {$this->varName} using non-integer values";
                break;
            case 'mixed':
                $msg = "test {$this->varName} using all potential values";
                break;
            case 'string':
                $msg = "test {$this->varName} using null or empty strings";
                break;
            case 'object':
            default:
                $msg = "mock {$this->varName} as {$this->tagType}";
                break;
        }

        $output->writeln("{$this->methodName} -- {$msg}");
    }
}
