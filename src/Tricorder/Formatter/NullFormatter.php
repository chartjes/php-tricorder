<?php
/**
 * This file is part of the tricorder.local.
 *
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Formatter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NullFormatter
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Formatter
 *
 * @deprecated Never used --- @todo Remove
 */
class NullFormatter implements Formatter
{
    /**
     * Output the suggestion.
     *
     * @param OutputInterface $output
     */
    public function outputMessage(OutputInterface $output)
    {
        // Do nothing
    }
}
