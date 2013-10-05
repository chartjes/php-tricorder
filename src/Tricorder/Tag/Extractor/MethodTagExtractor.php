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
class MethodTagExtractor
{
    /**
     * Extract the tags from the $method.
     *
     * @param SimpleXMLElement $method
     *
     * @return array
     */
    public function extractTags(SimpleXMLElement $method)
    {
        // Convert our tag information into an array for easy manipulation
        $methodTags = array();
        foreach ($method->docblock->tag as $tag) {
            array_push($methodTags, json_decode(json_encode((array)$tag), 1));
        }

        return $methodTags;
    }
}
