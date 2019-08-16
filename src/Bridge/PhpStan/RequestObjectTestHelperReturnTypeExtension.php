<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\TestHelper\RequestObjectTestHelper;
use PHPStan\Reflection\MethodReflection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Coupling required due to PHPStan design
 */
class RequestObjectTestHelperReturnTypeExtension extends AbstractFactoryReturnTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return RequestObjectTestHelper::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'buildFailingRequest' ||
            $methodReflection->getName() === 'buildUnvalidatedRequest' ||
            $methodReflection->getName() === 'buildValidatedRequest';
    }
}
