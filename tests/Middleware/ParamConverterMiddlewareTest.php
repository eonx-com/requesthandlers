<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Middleware;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Middleware\ParamConverterMiddleware;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class ParamConverterMiddlewareTest extends TestCase
{
    /**
     * Tests handle with bad route
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testHandleBadRoute(): void
    {
        $container = new Container();
        $controllerListener = $this->createMock(ControllerListener::class);
        $paramListener = $this->createMock(ParamConverterListener::class);

        $middleware = new ParamConverterMiddleware(
            $container,
            $controllerListener,
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
     * Tests handle
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testHandle(): void
    {
        $container = new Container();
        $container->instance('Class', new class {
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
            $container,
            $controllerListener,
            $paramListener
        );

        $request = new Request();
        $request->setRouteResolver(static function () {
            return [null, ['uses' => 'Class@method'], [
                'attribute' => 'value'
            ]];
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
}
