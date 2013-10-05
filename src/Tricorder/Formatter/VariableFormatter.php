<?php
/**
 * This file is part of the tricorder.local.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

/**
 * Class VariableFormatter
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formater
 */
class VariableFormatter
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
     * @param string $tagType
     * @param string $varName
     */
    public function __construct($tagType, $varName)
    {
        $this->tagType = $tagType;
        $this->varName = $varName;
    }

    /**
     * Returns the suggested message on what to test for a variable type.
     *
     * @return string
     */
    public function getMessage()
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

        return $msg;
    }
}
