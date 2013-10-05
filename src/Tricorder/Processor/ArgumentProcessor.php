<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Processor;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ArgumentProcessor
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Processor
 */
class ArgumentProcessor implements Processor
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
    public function process($methodName, array $tags, array $tricorderTags)
    {
        $processor = new ArgumentTypeProcessor($this->output);
        foreach ($tags as $tag) {
            $processor->process($methodName, $tag, $tricorderTags);
        }
    }
}
