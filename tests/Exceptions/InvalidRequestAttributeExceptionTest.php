<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
 */
class InvalidRequestAttributeExceptionTest extends TestCase
{
    /**
     * Tests methods on EntityNotFoundException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new InvalidRequestAttributeException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(60, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
