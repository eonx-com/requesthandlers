<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
 */
class UnsupportedClassExceptionTest extends TestCase
{
    /**
     * Tests methods on DoctrineDenormalizerMappingException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new UnsupportedClassException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(60, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
