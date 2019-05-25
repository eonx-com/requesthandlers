<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\KernelNotAvailableException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\KernelNotAvailableException
 */
class KernelNotAvailableExceptionTest extends TestCase
{
    /**
     * Tests that the abstract ValidationException works
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new KernelNotAvailableException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(20, $exception->getErrorCode());
    }
}
