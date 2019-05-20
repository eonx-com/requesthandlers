<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
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
}
