<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\ResponseSerialiserStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\SerialisableResponse;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware
 */
class SerialisableResponseMiddlewareTest extends TestCase
{
    /**
     * Tests that the middleware throws a MisconfiguredSerialiser exception when
     * the injected serialiser doesnt support normalising an instance of
     * SerialisableResponseInterface.
     *
     * @return void
     */
    public function testPassThroughNonResponse(): void
    {
        $responseSerialiser = new ResponseSerialiserStub([]);
        $middleware = new SerialisableResponseMiddleware($responseSerialiser);

        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle(new Request(), $next);

        static::assertSame('hello', $result);
    }

    /**
     * Tests that the normalised data is wrapped in a FormattedApiResponse with
     * the correct status code.
     *
     * @return void
     */
    public function testCorrectReturn(): void
    {
        $responseSerialiser = new ResponseSerialiserStub(['PURPLE' => 'ELEPHANTS']);
        $middleware = new SerialisableResponseMiddleware($responseSerialiser);

        $next = static function () {
            return new SerialisableResponse(202);
        };

        $expected = new FormattedApiResponse(['PURPLE' => 'ELEPHANTS'], 202);

        $result = $middleware->handle(new Request(), $next);

        static::assertEquals($expected, $result);
    }
}
