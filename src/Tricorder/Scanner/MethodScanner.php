<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Scanner;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Formatter\MethodFormatter;
use Tricorder\Processor\ArgumentProcessor;
use Tricorder\Processor\ReturnTypeProcessor;
use Tricorder\Tag\Extractor\MethodTagExtractor;

/**
 * Class MethodScanner
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Scanner
 */
class MethodScanner implements Scanner
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var SimpleXMLElement
     */
    private $method;

    /**
     * @param SimpleXMLElement $method
     * @param OutputInterface $output
     */
    public function __construct(SimpleXMLElement $method, OutputInterface $output)
    {
        $this->output = $output;
        $this->method = $method;

        $extractor    = new MethodTagExtractor();
        $this->tags   = $extractor->extractTags($method);
    }

    /**
     * Scan the $method for tags to process.
     */
    public function scan()
    {
        $tricorderTags = array_filter($this->tags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'tricorder') {
                    return true;
                }
            });

        // Check to see if we have any parameters that we need to test
        $paramTags = array_filter($this->tags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'param') {
                    return true;
                }
            });

        // Grab our method return information
        $returnTag = array_filter($this->tags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'return') {
                    return true;
                }
            });

        $argumentProcessor = new ArgumentProcessor($this->output);
        $argumentProcessor->process((string)$this->method->name, $paramTags, $tricorderTags);

        // Process ReturnType
        $processor = new ReturnTypeProcessor($this->output);
        $processor->process((string)$this->method->name, $returnTag, $tricorderTags);

        $methodFormatter = new MethodFormatter($this->method);
        $methodFormatter->outputMessage($this->output);
    }
}
