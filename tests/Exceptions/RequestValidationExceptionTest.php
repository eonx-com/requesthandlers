<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
 */
class RequestValidationExceptionTest extends TestCase
{
    /**
     * Tests that the abstract ValidationException works
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new RequestValidationExceptionStub($violations);

        self::assertSame(400, $exception->getStatusCode());
        self::assertSame($violations, $exception->getViolations());
    }

    /**
     * Tests base exception methods
     *
     * @return void
     */
    public function testBaseException(): void
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            'Message',
            'Mesasge',
            [],
            'root',
            'path',
            'invalid'
        ));

        $expected = [
            'path' => [
                'Message'
            ]
        ];

        $exception = new RequestValidationExceptionStub($violations);

        self::assertSame($expected, $exception->getErrors());
    }
}
