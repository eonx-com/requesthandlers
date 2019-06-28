<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\TestHelper\Exceptions;

use LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationList;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException
 */
class ValidationFailedExceptionTest extends TestCase
{
    /**
     * Tests methods on DoctrineDenormalizerMappingException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new ValidationFailedException($violations);

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(42, $exception->getErrorCode());
        self::assertSame(400, $exception->getStatusCode());
        self::assertSame($violations, $exception->getViolations());
    }
}
