<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use ReflectionClass;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer
 */
class PropertyNormalizerTest extends TestCase
{
    /**
     * Tests createChildContext
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreateChildContext(): void
    {
        $normalizer = new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter());

        // There is no simple way to test this method call, so lets use the Reflection approach.
        // Bad idea, do not copy.
        $reflectionClass = new ReflectionClass($normalizer);
        $method = $reflectionClass->getMethod('createChildContext');
        $method->setAccessible(true);

        $context = $method->invoke($normalizer, [], 'attributeName');

        static::assertSame('attribute_name', $context['attribute']);
    }

    /**
     * Tests createChildContext when no NameConverter exists
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testCreateChildContextWithoutNameConverter(): void
    {
        $normalizer = new PropertyNormalizer();

        // There is no simple way to test this method call, so lets use the Reflection approach.
        // Bad idea, do not copy.
        $reflectionClass = new ReflectionClass($normalizer);
        $method = $reflectionClass->getMethod('createChildContext');
        $method->setAccessible(true);

        $context = $method->invoke($normalizer, [], 'attributeName');

        static::assertSame('attributeName', $context['attribute']);
    }

    /**
     * Tests that denormalize adds extra parameters
     *
     * @return void
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalize(): void
    {
        $normalizer = new PropertyNormalizer();

        /** @var \Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub $result */
        $result = $normalizer->denormalize([], RequestObjectStub::class, null, [
            PropertyNormalizer::EXTRA_PARAMETERS => [
                'property' => 'value'
            ]
        ]);

        static::assertSame('value', $result->getProperty());
    }
}
