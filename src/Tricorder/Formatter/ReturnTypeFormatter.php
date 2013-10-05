<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReturnTypeFormatter
 *
 * Output the message for the return types.
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formatter
 */
class ReturnTypeFormatter implements Formatter
{
    /**
     * @var string
     */
    private $tagType;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param string $tagType
     * @param string $methodName
     */
    public function __construct($tagType, $methodName)
    {
        $this->tagType    = $tagType;
        $this->methodName = $methodName;
    }

    /**
     * Output the suggestion.
     *
     * @param OutputInterface $output
     */
    public function outputMessage(OutputInterface $output)
    {
        switch ($this->tagType) {
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
                $msg = "test method returns {$this->tagType} instances";
                break;
        }

        $output->writeln("{$this->methodName} -- {$msg}");
    }
}
