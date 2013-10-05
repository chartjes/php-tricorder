<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Tag\Extractor;

use SimpleXMLElement;

/**
 * Class MethodTagExtractor
 *
 * Extract the tags from the method.
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Tag\Extractor
 */
class MethodTagExtractor implements Extractor
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
     * Extract the tags.
     *
     * @return array
     */
    public function extractTags()
    {
        // Convert our tag information into an array for easy manipulation
        $methodTags = array();
        foreach ($this->method->docblock->tag as $tag) {
            array_push($methodTags, json_decode(json_encode((array)$tag), 1));
        }

        return $methodTags;
    }
}
