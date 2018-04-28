<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;

final class MethodArgumentAnalyzer
{
    public function hasMethodNthArgument(Node $node, int $position): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (count($node->args) < $position) {
            return false;
        }

        return true;
    }

    public function isMethodNthArgumentString(Node $node, int $position): bool
    {
        if (! $this->hasMethodNthArgument($node, $position)) {
            return false;
        }

        return $node->args[$position - 1]->value instanceof String_;
    }

    public function isMethodNthArgumentNull(Node $methodCallNode, int $position): bool
    {
        if (! $this->hasMethodNthArgument($methodCallNode, $position)) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $value = $methodCallNode->args[$position - 1]->value;
        if (! $value instanceof ConstFetch) {
            return false;
        }

        /** @var Identifier $nodeName */
        $nodeName = $value->name;

        return $nodeName->toLowerString() === 'null';
    }
}
