<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException
 */
class DoctrineParamConverterEntityNotFoundExceptionTest extends TestCase
{
    /**
     * Tests methods on EntityNotFoundException
     *
     * @return void
     */
    public function testExceptionMethods(): void
    {
        $exception = new DoctrineParamConverterEntityNotFoundException(null, null, 'stdClass', 'coupon');

        self::assertSame(1, $exception->getErrorSubCode());
        self::assertSame(11, $exception->getErrorCode());
        self::assertSame('stdClass', $exception->getEntityClass());
        self::assertSame('coupon', $exception->getParamName());
    }
}
