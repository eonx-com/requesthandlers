<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Coupling required due to PHPStan design
 */
class ObjectBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return ObjectBuilderInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'build' ||
            $methodReflection->getName() === 'buildWithContext';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PHPStan\Analyser\UndefinedVariableException
     * @throws \PHPStan\Broker\ClassAutoloadingException
     * @throws \PHPStan\Broker\ClassNotFoundException
     * @throws \PHPStan\Broker\FunctionNotFoundException
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     * @throws \PHPStan\Reflection\MissingPropertyFromReflectionException
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $argType = $scope->getType($methodCall->args[0]->value);
        if ($argType instanceof ConstantStringType === false) {
            return new MixedType();
        }

        return new ObjectType($argType->getValue());
    }
}
