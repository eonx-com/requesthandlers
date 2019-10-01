<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware;
use Symfony\Component\Validator\ConstraintViolationList;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Builder\ObjectValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware
 */
class ValidatingMiddlewareTest extends TestCase
{
    /**
     * Tests middleware handle method when nothing is violated
     *
     * @return void
     */
    public function testHandleLotsOfViolations(): void
    {
        $objectValidator = new ObjectValidatorStub(
            new RequestValidationExceptionStub(new ConstraintViolationList())
        );

        $middleware = new ValidatingMiddleware($objectValidator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, [
                'something' => 'unrelated',
                'object' => new RequestObjectStub()
            ]];
        });

        $next = static function () {
            return 'hello';
        };

        try {
            $middleware->handle($request, $next);
        } /** @noinspection BadExceptionsProcessingInspection */ catch (RequestValidationException $exception) {
            // The exception was thrown
            $this->addToAssertionCount(1);

            return;
        }

        static::fail('Exception was not thrown');
    }

    /**
     * Tests middleware handle method when there are no parameters
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testHandleNoParams(): void
    {
        $objectValidator = new ObjectValidatorStub();
        $middleware = new ValidatingMiddleware($objectValidator);

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
     * Tests middleware handle method when there are no parameters
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testHandleMissingParams(): void
    {
        $objectValidator = new ObjectValidatorStub();
        $middleware = new ValidatingMiddleware($objectValidator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null];
        });

        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }

    /**
     * Tests middleware handle method when there is no route
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testHandleNoRoute(): void
    {
        $objectValidator = new ObjectValidatorStub();
        $middleware = new ValidatingMiddleware($objectValidator);

        $request = new Request();
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
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testHandleNoViolations(): void
    {
        $objectValidator = new ObjectValidatorStub();
        $middleware = new ValidatingMiddleware($objectValidator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, [
                'something' => 'unrelated',
                'object' => new RequestObjectStub()
            ]];
        });

        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        self::assertSame('hello', $result);
    }
}
