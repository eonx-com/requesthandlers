<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\Bridge\PhpStan\RequestObjectTestHelperReturnTypeExtension;
use LoyaltyCorp\RequestHandlers\TestHelper\RequestObjectTestHelper;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Bridge\PhpStan\RequestObjectTestHelperReturnTypeExtension
 */
class RequestObjectTestHelperReturnTypeExtensionTest extends TestCase
{
    /**
     * Supported methods.
     *
     * @return string[][]
     */
    public function getSupportedMethods(): array
    {
        return [
            ['buildFailingRequest'],
            ['buildUnvalidatedRequest'],
            ['buildValidatedRequest']
        ];
    }

    /**
     * Tests what class the extension supports.
     *
     * @return void
     */
    public function testSupportedClass(): void
    {
        $extension = new RequestObjectTestHelperReturnTypeExtension();

        static::assertSame(RequestObjectTestHelper::class, $extension->getClass());
    }

    /**
     * Tests extension supports build method
     *
     * @param string $method
     *
     * @return void
     *
     * @dataProvider getSupportedMethods
     */
    public function testSupportedMethod(string $method): void
    {
        $extension = new RequestObjectTestHelperReturnTypeExtension();

        $method = new DummyMethodReflection($method);

        static::assertTrue($extension->isMethodSupported($method));
    }
}
