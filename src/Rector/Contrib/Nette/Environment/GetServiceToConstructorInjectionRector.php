<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Environment;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Naming\NameResolver;
use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * Environment::get('some_service') # where "some_service" is name of the service in container.
 *
 * into:
 * $this->someService # where "someService" is type of the service
 */
final class GetServiceToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var ServiceNameToTypeProviderInterface
     */
    private $serviceNameToTypeProvider;

    // @todo: service to type map - any manuall implementation works, use interface
    // and register to rector.yml

    public function __construct(
        NameResolver $nameResolver,
        ClassPropertyCollector $classPropertyCollector,
        NodeFactory $nodeFactory,
        MethodCallAnalyzer $methodCallAnalyzer,
        ServiceNameToTypeProviderInterface $serviceNameToTypeProvider
    ) {
        $this->nameResolver = $nameResolver;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->serviceNameToTypeProvider = $serviceNameToTypeProvider;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isStaticMethodCallTypeAndMethod($node, 'Nette\Environment', 'getService');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $serviceName = $methodCallNode->args[0]->value->value;

        $serviceToTypeMap = $this->serviceNameToTypeProvider->provide();
        if (! isset($serviceToTypeMap[$serviceName])) {
            return null;
        }

        $serviceType = $serviceToTypeMap[$serviceName];

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            $serviceType,
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}