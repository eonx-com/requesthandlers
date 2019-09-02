<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Responses;

use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\SerialisableResponse;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class AbstractSerialisableResponseTest extends TestCase
{
    /**
     * Tests that the status code is set and returned properly.
     *
     * @return void
     */
    public function testStatusCode(): void
    {
        $response = new SerialisableResponse(202);

        static::assertSame(202, $response->getStatusCode());
    }

    /**
     * Tests that the status code is set and returned properly.
     *
     * @return void
     */
    public function testStatusCodeNotSet(): void
    {
        $response = new SerialisableResponse(202);

        static::assertSame(202, $response->getStatusCode());
    }
}
