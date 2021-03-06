<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Processor;

use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Formatter\ReturnTypeFormatter;

/**
 * Class ReturnTypeProcessor
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Processor
 */
class ReturnTypeProcessor implements Processor
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
     * Look at the $tags and react accordingly
     *
     * @param string $methodName
     * @param array  $tag
     * @param array  $tricorderTags
     */
    public function process($methodName, array $tag, array $tricorderTags)
    {
        // Flatten the array a bit so we can check for attributes
        $tagInfo = array_shift($tag);
        if ($tagInfo !== null) {
            $tagType = $tagInfo['type'];
            $coverage = array();

            foreach ($tricorderTags as $tag) {
                if (isset($tag['@attributes']['description']) && preg_match('/^coversMethodReturns(.*?)Values\b/', $tag['@attributes']['description'], $matches)) {
                    array_push($coverage, strtolower($matches[1]));
                }
            }

            /**
             * Sometimes people send us return types like bool|string, so we need to
             * search for those and convert them to 'mixed'
             */
            if (stristr('|', $tagType) === true) {
                $tagType = 'mixed';
            }

            // Make sure to not bother with this if we already ran into this
            if (false === in_array($tagType, $coverage)) {
                $formatter = new ReturnTypeFormatter($tagType, $methodName);
                $formatter->outputMessage($this->output);
            }
        }
    }
}
