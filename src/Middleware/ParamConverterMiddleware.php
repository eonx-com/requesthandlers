<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Event\FilterControllerEvent;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;

class ParamConverterMiddleware
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * @var \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener
     */
    private $controllerListener;

    /**
     * @var \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener
     */
    private $listener;

    /**
     * Constructor
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener $controllerListener
     * @param \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener $paramConverterListener
     */
    public function __construct(
        Container $container,
        ControllerListener $controllerListener,
        ParamConverterListener $paramConverterListener
    ) {
        $this->container = $container;
        $this->controllerListener = $controllerListener;
        $this->listener = $paramConverterListener;
    }

    /**
     * Adds ParamConverter capabilities to controllers.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (\is_array($route) === false) {
            return $next($request);
        }

        // Resolve the controller callable based on the request.
        $controller = $this->resolveController($route);

        if ($controller !== null) {
            // Add laravel route attributes to the symfony request attribute bag so the
            // symfony dependencies will work as expected.
            $request->attributes->add($route[2]);

            // Create a faux FilterControllerEvent for use in the symfony dependencies below.
            $filterController = new FilterControllerEvent($controller, $request);

            // Process the controller method for any annotations that are relevant to us
            $this->controllerListener->onKernelController($filterController);

            // Process controller parameters with the ParamConverter instances
            $this->listener->onKernelController($filterController);

            // Put the Symfony request attributes back into the laravel route.
            foreach ($request->attributes as $key => $attribute) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $route[2][$key] = $attribute;
            }

            $request->setRouteResolver(static function () use ($route) {
                return $route;
            });
        }

        return $next($request);
    }

    /**
     * Resolves the route into a callable controller.
     *
     * @param mixed[] $route
     *
     * @return callable|null
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function resolveController(array $route): ?callable
    {
        $uses = $route[1]['uses'] ?? null;
        $split = \explode('@', $uses);

        $callableAction = [$this->container->make($split[0]), $split[1]];

        if (\is_callable($callableAction) === false) {
            return null;
        }

        return $callableAction;
    }
}
