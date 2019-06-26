<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidatingMiddleware
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

            $this->validateWithGroups(['PreValidate'], $parameter);

            $groups = $parameter->resolveValidationGroups();
            $groups[] = 'Default';

            $this->validateWithGroups($groups, $parameter);
        }

        return $next($request);
    }

    /**
     * Validates the request object with the specified groups.
     *
     * @param string[] $groups
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $requestObject
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    private function validateWithGroups(array $groups, RequestObjectInterface $requestObject): void
    {
        $violations = $this->validator->validate($requestObject, null, $groups);

        if ($violations->count() === 0) {
            return;
        }

        $exceptionClass = $requestObject::getExceptionClass();

        throw new $exceptionClass($violations);
    }
}
