<?php
/**
 * This file is part of the tricorder.local.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Formatter
 *
 * Contract for classes that report a suggestion to the user.
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formatter
 */
interface Formatter
{
    /**
     * Output the suggestion.
     *
     * @param OutputInterface  $output
     */
    public function outputMessage(OutputInterface $output);
}
