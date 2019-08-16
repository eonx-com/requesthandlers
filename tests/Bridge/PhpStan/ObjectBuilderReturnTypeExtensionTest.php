<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\Bridge\PhpStan\ObjectBuilderReturnTypeExtension;
use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Bridge\PhpStan\ObjectBuilderReturnTypeExtension
 */
class ObjectBuilderReturnTypeExtensionTest extends TestCase
{
    /**
     * Tests what class the extension supports.
     *
     * @return void
     */
    public function testSupportedClass(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        static::assertSame(ObjectBuilderInterface::class, $extension->getClass());
    }

    /**
     * Tests extension supports build method
     *
     * @return void
     */
    public function testSupportedMethodBuild(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('build');

        static::assertTrue($extension->isMethodSupported($method));
    }

    /**
     * Tests extension supports buildWithContext method
     *
     * @return void
     */
    public function testSupportedMethodBuildWithContext(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('buildWithContext');

        static::assertTrue($extension->isMethodSupported($method));
    }
}
