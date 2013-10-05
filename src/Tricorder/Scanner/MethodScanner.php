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
use Tricorder\Tag\Extractor\ParamTagExtractor;
use Tricorder\Tag\Extractor\ReturnTagExtractor;
use Tricorder\Tag\Extractor\TricorderTagExtractor;

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

        $extractor    = new MethodTagExtractor($method);
        $this->tags   = $extractor->extractTags();
    }

    /**
     * Scan the $method for tags to process.
     */
    public function scan()
    {
        $tricorderTagsExtractor = new TricorderTagExtractor($this->tags);
        $tricorderTags = $tricorderTagsExtractor->extractTags();

        $paramExtractor = new ParamTagExtractor($this->tags);
        $paramTags = $paramExtractor->extractTags();

        $returnTagExtractor = new ReturnTagExtractor($this->tags);
        $returnTag = $returnTagExtractor->extractTags();

        $argumentProcessor = new ArgumentProcessor($this->output);
        $argumentProcessor->process((string)$this->method->name, $paramTags, $tricorderTags);

        // Process ReturnType
        $processor = new ReturnTypeProcessor($this->output);
        $processor->process((string)$this->method->name, $returnTag, $tricorderTags);

        $methodFormatter = new MethodFormatter($this->method);
        $methodFormatter->outputMessage($this->output);
    }
}
