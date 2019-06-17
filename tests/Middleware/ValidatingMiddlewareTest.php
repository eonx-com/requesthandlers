<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware;
use Symfony\Component\Validator\ConstraintViolation;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
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
        $violation = new ConstraintViolation('message', null, [], null, null, null);
        $validator = new ValidatorStub([
            // No violations in PreValidate
            [],
            // Violation in normal validation
            [$violation]
        ]);

        $middleware = new ValidatingMiddleware($validator);

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
        } catch (RequestValidationException $exception) {
            static::assertContains($violation, $exception->getViolations());

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
     * Tests middleware handle method when there is no route
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
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
     * Tests middleware handle method when nothing is violated
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function testHandleNoViolations(): void
    {
        $validator = new ValidatorStub();

        $middleware = new ValidatingMiddleware($validator);

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

    /**
     * Tests PreValidate runs before primary validate
     *
     * @return void
     */
    public function testHandleViolationInPreValidate(): void
    {
        $violation1 = new ConstraintViolation('PreValidate', null, [], null, null, null);
        $violation2 = new ConstraintViolation('PostValidate', null, [], null, null, null);
        $validator = new ValidatorStub([
            // PreValidate violation
            [$violation1],
            // Violation in normal validation
            [$violation2]
        ]);

        $middleware = new ValidatingMiddleware($validator);

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, null, [
                'object' => new RequestObjectStub()
            ]];
        });

        $next = static function () {
            return 'hello';
        };

        try {
            $middleware->handle($request, $next);
        } catch (RequestValidationException $exception) {
            static::assertContains($violation1, $exception->getViolations());
            static::assertNotContains($violation2, $exception->getViolations());

            return;
        }

        static::fail('Exception was not thrown');
    }
}
