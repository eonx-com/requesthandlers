<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException
 */
class InvalidContentTypeExceptionTest extends TestCase
{
    /**
     * Tests methods on InvalidContentTypeException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new InvalidContentTypeException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(40, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
