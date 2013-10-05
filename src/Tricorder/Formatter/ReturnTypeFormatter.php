<?php
/**
 * This file is part of the tricorder.local.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

/**
 * Class ReturnTypeFormatter
 *
 * Output the message for the return types.
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formatter
 */
class ReturnTypeFormatter
{
    /**
     * @var string
     */
    private $tagType;

    /**
     * @param string $tagType
     */
    public function __construct($tagType)
    {
        $this->tagType = $tagType;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
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

        return $msg;
    }
}
