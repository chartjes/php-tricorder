<?php
/**
 * This file is part of the php-tricorder project
 *
 *  (c) Chris Hartjes (http://grihub.com/chartjes)
 */

namespace Tricorder\Scanner;

/**
 * Class MethodScanner
 *
 * @author Chris Hartjes (http://github.com/chartjes)
 *
 * @package Tricorder\Scanner
 */
class AttributeScanner implements Scanner
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $messages;

    /**
     * @param string $className
     * @param OutputInterface $output
     */
    public function __construct($filename)
    {
        $this->source = file_get_contents($filename);
        $this->messages = array("Scanning {$filename} for non-public class attributes");
    }

    /**
     * Scan the class to find attributes declared as non-public
     *
     * @return array
     */
    public function scan()
    {
        $parser = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);
        $ast = $parser->parse($this->source);
        $traverser = new \PhpParser\NodeTraverser;
        $visitor = new \Tricorder\Scanner\NodeVisitor;
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);
        $attributes = $visitor->getNonPublicAttributes();

        if (isset($attributes['protected'])) {
            $this->processProtected($attributes['protected']);
        }

        if (isset($attributes['private'])) {
            $this->processPrivate($attributes['private']);
        }

        return $this->messages;
    }

    protected function processProtected($attributes)
    {
        foreach ($attributes as $attribute) {
            $message = "\${$attribute} -- protected attributes can only be read, not altered or set";
            array_push($this->messages, $message);
        }
    }

    protected function processPrivate($attributes)
    {
        $messages = array();

        foreach ($attributes as $attribute) {
            $message = "\${$attribute} -- private attributes can only be read, not altered or set";
            array_push($this->messages, $message);
        }
    }
}
