<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
 */
class ResponseNormaliserExceptionTest extends TestCase
{
    /**
     * Tests methods on EntityNotFoundException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new ResponseNormaliserException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(80, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
