<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException;
use stdClass;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\SerializerStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder
 */
class ObjectBuilderTest extends TestCase
{
    /**
     * Tests the build happy path.
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
        $validator = new ValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $result = $builder->build(TestRequest::class, '');

        static::assertSame($expected, $result);
    }

    /**
     * Tests the buildWithContext happy path.
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
        $validator = new ValidatorStub();
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
        $validator = new ValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $this->expectException(MisconfiguredSerializerException::class);
        $this->expectExceptionMessage(
            'The serializer returned an object of type "stdClass" but it is not an instance of "Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest"' // phpcs:ignore
        );

        $builder->build(TestRequest::class, '');
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
        $validator = new ValidatorStub();
        $builder = $this->getBuilder($serializer, $validator);

        $this->expectException(UnsupportedClassException::class);
        $this->expectExceptionMessage(
            'The supplied class "stdClass" is not supported. It must be an instance of "LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface"' // phpcs:ignore
        );

        $builder->build(stdClass::class, '');
    }

    /**
     * Tests that ensure validated does not throw when the object is valid.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testEnsureValidated(): void
    {
        $request = new TestRequest();

        $validator = new ValidatorStub();
        $builder = $this->getBuilder(null, $validator);

        $builder->ensureValidated($request);

        // No exception was thrown.
        $this->addToAssertionCount(1);
    }

    /**
     * Tests that ensure validated throws when the object is invalid in PreValidate.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testEnsureValidatedThrows(): void
    {
        $request = new TestRequest();

        $validator = new ValidatorStub([
            [], // PreValidate
            [ // Primary Validate
                new ConstraintViolation(
                    'Message',
                    'Message',
                    [],
                    '',
                    '',
                    ''
                )
            ]
        ]);

        $builder = $this->getBuilder(null, $validator);

        $this->expectException(RequestValidationException::class);

        $builder->ensureValidated($request);
    }

    /**
     * Tests that ensure validated throws when the object is invalid in PreValidate.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testEnsureValidatedThrowsPreValidate(): void
    {
        $request = new TestRequest();

        $validator = new ValidatorStub([[
            new ConstraintViolation(
                'Message',
                'Message',
                [],
                '',
                '',
                ''
            )
        ]]);

        $builder = $this->getBuilder(null, $validator);

        $this->expectException(RequestValidationException::class);

        $builder->ensureValidated($request);
    }

    /**
     * Gets the builder under test.
     *
     * @param \Symfony\Component\Serializer\SerializerInterface|null $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface|null $validator
     *
     * @return \LoyaltyCorp\RequestHandlers\Builder\ObjectBuilder
     */
    private function getBuilder(
        ?SerializerInterface $serializer = null,
        ?ValidatorInterface $validator = null
    ): ObjectBuilder {
        return new ObjectBuilder(
            $serializer ?? new SerializerStub(),
            $validator ?? new ValidatorStub()
        );
    }

//    /**
//     * Tests PreValidate runs before primary validate
//     *
//     * @return void
//     */
//    public function testHandleViolationInPreValidate(): void
//    {
//        $violation1 = new ConstraintViolation('PreValidate', null, [], null, null, null);
//        $violation2 = new ConstraintViolation('StandardValidation', null, [], null, null, null);
//        $validator = new ValidatorStub([
//            // PreValidate violation
//            [$violation1],
//            // Violation in normal validation
//            [$violation2]
//        ]);
//
//        $middleware = new ValidatingMiddleware($validator);
//
//        $request = new Request();
//        $request->setRouteResolver(static function () {
//            return [null, null, [
//                'object' => new RequestObjectStub()
//            ]];
//        });
//
//        $next = static function () {
//            return 'hello';
//        };
//
//        try {
//            $middleware->handle($request, $next);
//        } catch (RequestValidationException $exception) {
//            static::assertContains($violation1, $exception->getViolations());
//            static::assertNotContains($violation2, $exception->getViolations());
//
//            return;
//        }
//
//        static::fail('Exception was not thrown');
//    }
}
