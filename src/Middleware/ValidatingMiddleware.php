<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;

final class ValidatingMiddleware
{
    /**
     * @var \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface
     */
    private $objectBuilder;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface $objectBuilder
     */
    public function __construct(ObjectBuilderInterface $objectBuilder)
    {
        $this->objectBuilder = $objectBuilder;
    }

    /**
     * Find any RequestDtoInterface objects in the routing parameters and
     * make sure they validate.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (\is_array($route) === false) {
            return $next($request);
        }

        /** @noinspection ForeachSourceInspection Laravel's $route parameter is an array of properties */
        foreach ($route[2] ?? [] as $parameter) {
            if (($parameter instanceof RequestObjectInterface) === false) {
                continue;
            }

            /**
             * @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $parameter
             */

            $this->objectBuilder->ensureValidated($parameter);
        }

        return $next($request);
    }
}
