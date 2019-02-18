<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class ClassConstMaintainer
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        NameResolver $nameResolver,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->nameResolver = $nameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return ClassConst[]
     */
    public function getAllClassConstFetch(ClassConst $classConstNode): array
    {
        $classNode = $classConstNode->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        return $this->betterNodeFinder->find($classNode, function (Node $node) use ($classConstNode) {
            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $classConstNode)) {
                return false;
            }

            // property + static fetch
            if (! $node instanceof ClassConstFetch) {
                return false;
            }

            // is it the name match?
            if ($this->nameResolver->resolve($node) !== $this->nameResolver->resolve($classConstNode)) {
                return false;
            }

            return true;
        });
    }
}
