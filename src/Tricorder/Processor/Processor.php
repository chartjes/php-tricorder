<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Processor;

/**
 * Class Processor
 *
 * Contract for classes that process the data, to determine if a suggestion is needed.
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Processor
 */
interface Processor
{
    /**
     * Look at the $tags and react accordingly
     *
     * @param string $methodName
     * @param array  $tag
     * @param array  $tricorderTags
     */
    public function process($methodName, array $tag, array $tricorderTags);
}
