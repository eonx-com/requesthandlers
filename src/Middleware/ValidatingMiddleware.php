<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Request\RequestDtoInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatingMiddleware
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Find any RequestDtoInterface objects in the routing parameters and
     * make sure they validate.
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

        /** @noinspection ForeachSourceInspection Laravel's $route parameter is an array of properties */
        foreach ($route[2] as $parameter) {
            if (($parameter instanceof RequestDtoInterface) === false) {
                continue;
            }

            /**
             * @var \LoyaltyCorp\RequestHandlers\Request\RequestDtoInterface $parameter
             */

            $groups = $parameter->resolveValidationGroups();
            $groups[] = 'Default';

            $violations = $this->validator->validate(
                $parameter,
                null,
                $groups
            );

            if ($violations->count() === 0) {
                continue;
            }

            $exceptionClass = $parameter::getExceptionClass();

            throw new $exceptionClass($violations);
        }

        return $next($request);
    }
}
