<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use Symfony\Component\Validator\ConstraintViolationList;
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
        $exception = new class($violations) extends RequestValidationException {
            /**
             * {@inheritdoc}
             */
            public function getErrorCode(): int
            {
                return 1;
            }

            /**
             * {@inheritdoc}
             */
            public function getErrorSubCode(): int
            {
                return 1;
            }
        };

        self::assertSame(400, $exception->getStatusCode());
        self::assertSame($violations, $exception->getViolations());
    }
}
