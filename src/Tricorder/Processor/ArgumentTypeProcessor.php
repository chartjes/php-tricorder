<?php
/**
 * This file is part of the tricorder.local.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Processor;

use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Formatter\VariableFormatter;

/**
 * Class ArgumentTypeProcessor
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Processor
 */
class ArgumentTypeProcessor
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Look at the argument type and react accordingly
     *
     * @param string $methodName
     * @param array  $tag
     * @param array  $tricorderTags
     */
    public function processArgumentType($methodName, array $tag, array $tricorderTags)
    {
        $varName = $tag['@attributes']['variable'] ?: null;
        $tagType = $tag['type'];

        $coverage = array();
        foreach ($tricorderTags as $tag) {
            if (isset($tag['@attributes']['description']) && preg_match('/^coversMethodAccepts(.*?)Values\b/', $tag['@attributes']['description'], $matches)) {
                array_push($coverage, strtolower($matches[1]));
            }
        }

        /**
         * Sometimes people send us param types like bool|string, so we need to
         * search for those and convert them to 'mixed'
         */
        if (stristr('|', $tagType) === true) {
            $tagType = 'mixed';
        }

        // If tag is already in coverage, do not process it
        if (false === in_array($tagType, $coverage)) {
            $formatter = new VariableFormatter($tagType, $varName, $methodName);
            $formatter->outputMessage($this->output);
        }
    }
}
