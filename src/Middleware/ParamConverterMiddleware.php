<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use EoneoPay\Utils\Bridge\Lumen\Resolvers\ControllerResolver;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Event\FilterControllerEvent;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;

final class ParamConverterMiddleware
{
    /**
     * @var \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener
     */
    private $controllerListener;

    /**
     * @var \EoneoPay\Utils\Bridge\Lumen\Resolvers\ControllerResolver
     */
    private $controllerResolver;

    /**
     * @var \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener
     */
    private $listener;

    /**
     * Constructor
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener $controllerListener
     * @param \EoneoPay\Utils\Bridge\Lumen\Resolvers\ControllerResolver $controllerResolver
     * @param \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener $converterListener
     */
    public function __construct(
        ControllerListener $controllerListener,
        ControllerResolver $controllerResolver,
        ParamConverterListener $converterListener
    ) {
        $this->controllerListener = $controllerListener;
        $this->controllerResolver = $controllerResolver;
        $this->listener = $converterListener;
    }

    /**
     * Adds ParamConverter capabilities to controllers.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (\is_array($route) === false) {
            return $next($request);
        }

        // Resolve the controller callable based on the request.
        $controller = $this->controllerResolver->resolve($route);

        if ($controller === null) {
            // Controller is not callable, continue with other middleware
            return $next($request);
        }

        // Add laravel route attributes to the symfony request attribute bag so the
        // symfony dependencies will work as expected.
        $request->attributes->add($route[2] ?? []);

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

        return $next($request);
    }
}
