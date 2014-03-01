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
     * @param string $className
     * @param OutputInterface $output
     */
    public function __construct($source)
    {
        $this->source = $source;
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
        $messages = array();

        if (isset($attributes['protected'])) {
            $messages = array_merge($this->processProtected($attributes['protected']), $messages);
        }

        if (isset($attributes['private'])) {
            $messages = array_merge($this->processPrivate($attributes['private']), $messages);
        }

        return $messages;
    }

    protected function processProtected($attributes)
    {
        $messages = array();

        foreach ($attributes as $attribute) {
            $messages[] = "\${$attribute} -- protected attributes can only be read, not altered or set";
        }

        return $messages;
    }

    protected function processPrivate($attributes)
    {
        $messages = array();

        foreach ($attributes as $attribute) {
            $messages[] = "\${$attribute} -- private attributes can only be read, not altered or set";
        }

        return $messages;
    }
}
