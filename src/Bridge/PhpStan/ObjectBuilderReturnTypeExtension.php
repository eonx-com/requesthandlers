<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use PHPStan\Reflection\MethodReflection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Coupling required due to PHPStan design
 */
class ObjectBuilderReturnTypeExtension extends AbstractFactoryReturnTypeExtension
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
}
