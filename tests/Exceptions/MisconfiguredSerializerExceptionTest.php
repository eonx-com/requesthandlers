<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
 */
class MisconfiguredSerializerExceptionTest extends TestCase
{
    /**
     * Tests methods on DoctrineDenormalizerMappingException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new MisconfiguredSerializerException();

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(70, $exception->getErrorCode());
        self::assertSame(500, $exception->getStatusCode());
    }
}
