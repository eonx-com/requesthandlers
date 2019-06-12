<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
 */
class DoctrineDenormalizerMappingExceptionTest extends TestCase
{
    /**
     * Tests methods on DoctrineDenormalizerMappingException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new DoctrineDenormalizerMappingException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(30, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
