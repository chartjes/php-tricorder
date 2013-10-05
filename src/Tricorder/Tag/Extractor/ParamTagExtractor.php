<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Tag\Extractor;

/**
 * Class ParamTagExtractor
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Tag\Extractor
 */
class ParamTagExtractor implements Extractor
{
    /**
     * @var array
     */
    private $tags;

    /**
     * @param array $tags
     */
    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Extract the tags.
     *
     * @return array
     */
    public function extractTags()
    {
        $closure = function($tag) {
            if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'param') {
                return true;
            }
        };

        // Check to see if we have any parameters that we need to test
        return array_filter($this->tags, $closure);
    }
}
