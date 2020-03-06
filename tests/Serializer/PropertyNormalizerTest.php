<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use EoneoPay\Utils\AnnotationReader;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use ReflectionClass;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\DiscriminatedRequest;
use Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PropertyNormalizerTest extends TestCase
{
    /**
     * Tests createChildContext
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testCreateChildContext(): void
    {
        $normalizer = new PropertyNormalizer(
            new AnnotationReader(),
            null,
            new CamelCaseToSnakeCaseNameConverter()
        );

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
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testCreateChildContextWithoutNameConverter(): void
    {
        $normalizer = new PropertyNormalizer(new AnnotationReader());

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
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testDenormalize(): void
    {
        $normalizer = new PropertyNormalizer(new AnnotationReader());

        /** @var \Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub $result */
        $result = $normalizer->denormalize([], RequestObjectStub::class, null, [
            PropertyNormalizer::EXTRA_PARAMETERS => [
                'property' => 'value'
            ]
        ]);

        static::assertSame('value', $result->getProperty());
    }

    /**
     * Tests that the normalizer does not allow attributes that are annotated with InjectedFromContext
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \ReflectionException
     */
    public function testInjectedFromContextClassName(): void
    {
        $normalizer = new PropertyNormalizer(new AnnotationReader());

        // There is no simple way to test this method call, so lets use the Reflection approach.
        // Bad idea, do not copy.
        $reflectionClass = new ReflectionClass($normalizer);
        $method = $reflectionClass->getMethod('isAllowedAttribute');
        $method->setAccessible(true);

        static::assertTrue($method->invoke($normalizer, ThingRequest::class, 'amount'));
        static::assertFalse($method->invoke($normalizer, ThingRequest::class, 'baz'));
        static::assertFalse($method->invoke($normalizer, new ThingRequest(), 'baz'));
    }

    /**
     * Tests that when deserialising a discriminator mapped class that having invalid
     * data present for type discrimination will not cause an exception.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testTypeDiscriminationWithInvalidType(): void
    {
        $annotationReader = new AnnotationReader();
        $metadata = new ClassMetadataFactory(new AnnotationLoader(
            $annotationReader
        ));
        $resolver = new ClassDiscriminatorFromClassMetadata($metadata);

        $normalizer = new PropertyNormalizer(
            $annotationReader,
            $metadata,
            null,
            null,
            $resolver
        );

        $result = $normalizer->denormalize([
            'type' => 'nope'
        ], DiscriminatedRequest::class, 'json');

        self::assertInstanceOf(DiscriminatedRequest::class, $result);
        self::assertSame('nope', $result->getType());
    }

    /**
     * Tests that when deserialising a discriminator mapped class that not having the
     * data present for type discrimination will not cause an exception.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testTypeDiscriminationWithoutAType(): void
    {
        $annotationReader = new AnnotationReader();
        $metadata = new ClassMetadataFactory(new AnnotationLoader(
            $annotationReader
        ));
        $resolver = new ClassDiscriminatorFromClassMetadata($metadata);

        $normalizer = new PropertyNormalizer(
            $annotationReader,
            $metadata,
            null,
            null,
            $resolver
        );

        $result = $normalizer->denormalize([], DiscriminatedRequest::class, 'json');

        self::assertInstanceOf(DiscriminatedRequest::class, $result);
    }

    /**
     * Tests that when deserialising a discriminator mapped class that will accept
     * an integer data type.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testTypeDiscriminationWithNonStringType(): void
    {
        $annotationReader = new AnnotationReader();
        $metadata = new ClassMetadataFactory(new AnnotationLoader(
            $annotationReader
        ));
        $resolver = new ClassDiscriminatorFromClassMetadata($metadata);

        $normalizer = new PropertyNormalizer(
            $annotationReader,
            $metadata,
            null,
            null,
            $resolver
        );

        $result = $normalizer->denormalize([
            'type' => 5
        ], DiscriminatedRequest::class, 'json');

        self::assertInstanceOf(DiscriminatedRequest::class, $result);
    }

    /**
     * Tests that when deserialising a discriminator mapped class that having invalid
     * data present for type discrimination will not cause an exception.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testTypeDiscriminationWithoutResolver(): void
    {
        $normalizer = new PropertyNormalizer(new AnnotationReader());

        $result = $normalizer->denormalize([
            'type' => 'nope'
        ], DiscriminatedRequest::class, 'json');

        self::assertInstanceOf(DiscriminatedRequest::class, $result);
        self::assertSame('nope', $result->getType());
    }
}
