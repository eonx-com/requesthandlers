<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware;
use Symfony\Component\Validator\ConstraintViolation;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestDtoStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware
 */
class ValidatingMiddlewareTest extends TestCase
{
    /**
     * Tests middleware handle method when there is no route
     *
     * @return void
     */
    public function testHandleNoRoute(): void
    {
        $validator = new ValidatorStub();

        $middleware = new ValidatingMiddleware($validator);

        $request = new Request();
        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }

    /**
     * Tests middleware handle method when there are no parameters
     *
     * @return void
     */
    public function testHandleNoParams(): void
    {
        $validator = new ValidatorStub();

        $middleware = new ValidatingMiddleware($validator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, []];
        });

        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }

    /**
     * Tests middleware handle method when nothing is violated
     *
     * @return void
     */
    public function testHandleNoViolations(): void
    {
        $validator = new ValidatorStub();

        $middleware = new ValidatingMiddleware($validator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, [
                'something' => 'unrelated',
                'object' => new RequestDtoStub()
            ]];
        });

        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }

    /**
     * Tests middleware handle method when nothing is violated
     *
     * @return void
     */
    public function testHandleLotsOfViolations(): void
    {
        $validator = new ValidatorStub([
            new ConstraintViolation('message', null, [], null, null, null)
        ]);

        $middleware = new ValidatingMiddleware($validator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, [
                'something' => 'unrelated',
                'object' => new RequestDtoStub()
            ]];
        });

        $next = static function () {
            return 'hello';
        };

        $this->expectException(RequestValidationException::class);

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }
}
