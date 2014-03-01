<?php

/**
 * A node visitor object that looks for non-public attributes in the AST
 * of PHP code as it is traversed
 *
 * @author Chris Hartjes
 * @version 0.1
 */

namespace Tricorder\Scanner;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SplFileInfo;
use SplStack;

class NodeVisitor extends NodeVisitorAbstract
{
    protected $nonPublicAttributes;

    public function enterNode(Node $node)
    {
        // If we find a protected attribute, push it onto the stack
        if ($node instanceof \PhpParser\Node\Stmt\Property) {
            $props = $node->props[0];

            if ($node->isProtected()) {
                $this->nonPublicAttributes['protected'][] = $props->name;
            } else if ($node->isPrivate()) {
                $this->nonPublicAttributes['private'][] = $props->name;
            }
        }
    }

    public function getNonPublicAttributes()
    {
        return $this->nonPublicAttributes;
    }
}
