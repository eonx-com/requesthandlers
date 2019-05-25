<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException
 */
class DoctrineParamConverterMisconfiguredExceptionTest extends TestCase
{
    /**
     * Tests methods on EntityNotFoundException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new DoctrineParamConverterMisconfiguredException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(10, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
