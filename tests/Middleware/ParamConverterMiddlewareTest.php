<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use EoneoPay\Utils\Bridge\Lumen\Resolvers\ControllerResolver;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\EventListeners\ParamConverterListener;
use LoyaltyCorp\RequestHandlers\Middleware\ParamConverterMiddleware;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Middleware\ParamConverterMiddleware
 */
class ParamConverterMiddlewareTest extends TestCase
{
    /**
     * Tests handle
     *
     * @return void
     */
    public function testHandle(): void
    {
        $container = new Container();
        $container->instance('Class', new class
        {
            /**
             * Method for test
             *
             * @return void
             */
            public function method(): void
            {
            }
        });
        $controllerListener = $this->createMock(ControllerListener::class);
        $paramListener = $this->createMock(ParamConverterListener::class);

        $middleware = new ParamConverterMiddleware(
            $controllerListener,
            new ControllerResolver($container),
            $paramListener
        );

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [
                null,
                ['uses' => 'Class@method'],
                [
                    'attribute' => 'value'
                ]
            ];
        });
        $next = static function () {
            return 'hello';
        };

        $middleware->handle($request, $next);

        /** @var mixed[] $route */
        $route = $request->route();

        self::assertSame('value', $request->attributes->get('attribute'));
        self::assertSame('value', $route[2]['attribute']);
    }

    /**
     * Tests handle route with no parameters
     *
     * @return void
     */
    public function testHandleRouteNoParams(): void
    {
        $container = new Container();
        $container->instance('Class', new class
        {
            /**
             * Method for test
             *
             * @return void
             */
            public function method(): void
            {
            }
        });
        $controllerListener = $this->createMock(ControllerListener::class);
        $paramListener = $this->createMock(ParamConverterListener::class);

        $middleware = new ParamConverterMiddleware(
            $controllerListener,
            new ControllerResolver($container),
            $paramListener
        );

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [
                null,
                ['uses' => 'Class@method']
            ];
        });
        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        // No fatal error occurred
        static::assertSame('hello', $result);
    }

    /**
     * Tests handle with bad route
     *
     * @return void
     */
    public function testHandleBadRoute(): void
    {
        $container = new Container();
        $controllerListener = $this->createMock(ControllerListener::class);
        $paramListener = $this->createMock(ParamConverterListener::class);

        $middleware = new ParamConverterMiddleware(
            $controllerListener,
            new ControllerResolver($container),
            $paramListener
        );

        $request = new Request();
        $next = static function () {
            return 'hello';
        };

        $middleware->handle($request, $next);

        $this->addToAssertionCount(1);
    }

    /**
     * Test handle works gracefully when the route calls non existent action
     *
     * @return void
     */
    public function testHandleWhenRouteActionDoesNotExist(): void
    {
        $container = new Container();
        $container->instance('Class', new class
        {
            // empty class with no actions
        });

        $controllerListener = $this->createMock(ControllerListener::class);
        $paramListener = $this->createMock(ParamConverterListener::class);

        $middleware = new ParamConverterMiddleware(
            $controllerListener,
            new ControllerResolver($container),
            $paramListener
        );

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [
                null,
                ['uses' => 'Class@method']
            ];
        });
        $next = static function () {
            return 'hello';
        };

        $result = $middleware->handle($request, $next);

        // assert the control is passed to next middleware
        self::assertSame('hello', $result);
    }
}
