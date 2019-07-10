<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
 */
class ParamConverterMisconfiguredExceptionTest extends TestCase
{
    /**
     * Tests methods on EntityNotFoundException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new ParamConverterMisconfiguredException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(50, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
