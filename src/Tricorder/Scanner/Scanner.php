<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Scanner;

/**
 * Class Scanner
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Scanner
 */
interface Scanner
{
    /**
     * Scan bits of code to suggest improvements.
     */
    public function scan();
}
