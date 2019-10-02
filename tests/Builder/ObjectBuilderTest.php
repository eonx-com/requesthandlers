<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface;
use LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException;
use stdClass;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Builder\ObjectValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\SerializerStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder
 */
class ObjectBuilderTest extends TestCase
{
    /**
     * Tests that build returns the object the serializer returned.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     */
    public function testBuild(): void
    {
        $expected = new TestRequest();

        $serializer = new SerializerStub($expected);
        $validator = new ObjectValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $result = $builder->build(TestRequest::class, '');

        static::assertSame($expected, $result);
    }

    /**
     * Tests the build happy path.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     */
    public function testBuildNotRequestObject(): void
    {
        $serializer = new SerializerStub(new stdClass());
        $validator = new ObjectValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $this->expectException(UnsupportedClassException::class);
        $this->expectExceptionMessage(
            'The supplied class "stdClass" is not supported. It must be an instance of "LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface"' // phpcs:ignore
        );

        $builder->build(stdClass::class, '');
    }

    /**
     * Tests the buildWithContext returns the expected.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     */
    public function testBuildWithContext(): void
    {
        $expected = new TestRequest();

        $serializer = new SerializerStub($expected);
        $validator = new ObjectValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $result = $builder->buildWithContext(TestRequest::class, []);

        static::assertSame($expected, $result);
    }

    /**
     * Tests the build happy path.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     */
    public function testBuildWrongObject(): void
    {
        $serializer = new SerializerStub(new stdClass());
        $validator = new ObjectValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $this->expectException(MisconfiguredSerializerException::class);
        $this->expectExceptionMessage(
            'The serializer returned an object of type "stdClass" but it is not an instance of "Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest"' // phpcs:ignore
        );

        $builder->build(TestRequest::class, '');
    }

    /**
     * Gets the builder under test.
     *
     * @param \Symfony\Component\Serializer\SerializerInterface|null $serializer
     * @param \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface|null $validator
     *
     * @return \LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder
     */
    private function getBuilder(
        ?SerializerInterface $serializer = null,
        ?ObjectValidatorInterface $validator = null
    ): ObjectBuilder {
        return new ObjectBuilder(
            $serializer ?? new SerializerStub(),
            $validator ?? new ObjectValidatorStub()
        );
    }
}
