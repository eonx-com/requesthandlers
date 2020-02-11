<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException
 */
class DoctrineDenormalizerEntityFinderClassExceptionTest extends TestCase
{
    /**
     * Tests that the error codes on the expection match the expected.
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new DoctrineDenormalizerEntityFinderClassException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(31, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
