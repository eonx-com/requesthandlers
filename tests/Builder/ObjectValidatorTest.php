<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\ObjectValidator;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Tests\LoyaltyCorp\RequestHandlers\Fixtures\TestRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Builder\ObjectValidator
 */
class ObjectValidatorTest extends TestCase
{
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

        $validator = new ObjectValidator(new ValidatorStub());

        $validator->ensureValidated($request);

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

        $innerValidator = new ValidatorStub([
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

        $validator = new ObjectValidator($innerValidator);

        $this->expectException(RequestValidationException::class);

        $validator->ensureValidated($request);
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

        $innerValidator = new ValidatorStub([[
            // Violations in PreValidate
            new ConstraintViolation(
                'Message',
                'Message',
                [],
                '',
                '',
                ''
            )
        ]]);

        $validator = new ObjectValidator($innerValidator);

        $this->expectException(RequestValidationException::class);

        $validator->ensureValidated($request);
    }
}
