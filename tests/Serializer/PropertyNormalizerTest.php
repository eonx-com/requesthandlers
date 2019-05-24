<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer\PropertyNormalizerStub;
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
     */
    public function testCreateChildContext(): void
    {
        $normalizer = new PropertyNormalizerStub(null, new CamelCaseToSnakeCaseNameConverter());

        $context = $normalizer->createChildContext([], 'attributeName');

        static::assertSame('attribute_name', $context['attribute']);
    }

    /**
     * Tests createChildContext when no NameConverter exists
     *
     * @return void
     */
    public function testCreateChildContextWithoutNameConverter(): void
    {
        $normalizer = new PropertyNormalizerStub();

        $context = $normalizer->createChildContext([], 'attributeName');

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
