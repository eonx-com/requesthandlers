<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException;
use LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\SerialisableResponse;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\NormaliserStub;
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
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testNotSupportedResponse(): void
    {
        $normaliser = new NormaliserStub(null, false);

        $middleware = new SerialisableResponseMiddleware([], $normaliser);

        $next = static function () {
            return new SerialisableResponse(200);
        };

        $this->expectException(MisconfiguredSerializerException::class);
        $this->expectExceptionMessage('The serialiser is not configured to support serialisable response objects.');

        $middleware->handle(new Request(), $next);
    }

    /**
     * Tests that the middleware throws a MisconfiguredSerialiser exception when
     * the injected serialiser doesnt support normalising an instance of
     * SerialisableResponseInterface.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testPassThroughNonResponse(): void
    {
        $normaliser = new NormaliserStub(null, true);
        $middleware = new SerialisableResponseMiddleware([], $normaliser);

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
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testCorrectReturn(): void
    {
        $normaliser = new NormaliserStub('PURPLE ELEPHANTS', true);
        $middleware = new SerialisableResponseMiddleware([], $normaliser);

        $next = static function () {
            return new SerialisableResponse(202);
        };

        $expected = new FormattedApiResponse('PURPLE ELEPHANTS', 202);

        $result = $middleware->handle(new Request(), $next);

        static::assertEquals($expected, $result);
    }

    /**
     * Tests that the middleware wraps any serialiser exception with its own.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testThrowsOnSerialiserFailure(): void
    {
        $normaliser = new NormaliserStub(new CircularReferenceException(), true);
        $middleware = new SerialisableResponseMiddleware([], $normaliser);

        $next = static function () {
            return new SerialisableResponse(200);
        };

        $this->expectException(ResponseNormaliserException::class);
        $this->expectExceptionMessage('An exception occurred while trying to serialise a response.');

        $middleware->handle(new Request(), $next);
    }
}
