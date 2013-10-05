<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Tag\Extractor;

/**
 * Class Extractor
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Tag\Extractor
 */
interface Extractor
{
    /**
     * Extract the tags.
     *
     * @return array
     */
    public function extractTags();
}
