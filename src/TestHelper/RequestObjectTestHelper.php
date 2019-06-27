<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\TestHelper;

use Illuminate\Contracts\Container\Container;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This test helper exists to have a single place where logic used by test cases
 * can be centralised.
 *
 * It contains basic methods for creating validated and unvalidated request
 * objects and extract properties from those request objects.
 *
 * This class should not be used by any normal services.
 *
 * @internal
 */
final class RequestObjectTestHelper
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $app;

    /**
     * Constructor
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Builds a failing request and returns the validation errors raised by the failure.
     *
     * @param string $class
     * @param string $json
     * @param mixed[]|null $context
     *
     * @return mixed[]
     */
    public function buildFailingRequest(
        string $class,
        string $json,
        ?array $context = null
    ): array {
        try {
            $this->buildValidatedRequest($class, $json, $context);
        } catch (ValidationFailedException $exception) {
            return $exception->getErrors();
        }

        throw new \RuntimeException('There were no validation errors.');
    }

    /**
     * Builds an unvalidated request object. The context property will set and overwrite
     * any properties on the request object with the supplied values.
     *
     * @param string $class
     * @param string $json
     * @param mixed[]|null $context
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface
     */
    public function buildUnvalidatedRequest(
        string $class,
        string $json,
        ?array $context = null
    ): RequestObjectInterface {
        /** @var \Symfony\Component\Serializer\SerializerInterface $serializer */
        $serializer = $this->app->get('requesthandlers_serializer');

        /** @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance */
        $instance = $serializer->deserialize(
            $json,
            $class,
            'json',
            [
                PropertyNormalizer::EXTRA_PARAMETERS => $context ?? []
            ]
        );

        return $instance;
    }

    /**
     * Builds a validated request object. The context property will set and overwrite
     * any properties on the request object with the supplied values.
     *
     * @param string $class
     * @param string $json
     * @param mixed[]|null $context
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface
     *
     * @throws \LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException
     */
    public function buildValidatedRequest(
        string $class,
        string $json,
        ?array $context = null
    ): RequestObjectInterface {
        $instance = $this->buildUnvalidatedRequest($class, $json, $context);
        $violations = $this->validateRequest($instance);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($violations);
        }

        return $instance;
    }

    /**
     * Returns an array of properties and their values when those properties have getters.
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $object
     *
     * @return mixed[]
     */
    public function getRequestProperties(RequestObjectInterface $object): array
    {
        $interfaceMethods = \get_class_methods(RequestObjectInterface::class);
        $instanceMethods = \get_class_methods($object);

        $methodsToCheck = \array_filter(
            \array_diff($instanceMethods, $interfaceMethods),
            static function (string $method): bool {
                return \strncmp($method, 'get', 3) === 0;
            }
        );

        $actual = [];
        foreach ($methodsToCheck as $method) {
            $property = \lcfirst(\substr($method, 3));

            $callable = [$object, $method];
            if (\is_callable($callable) === false) {
                // @codeCoverageIgnoreStart
                // Unable to be tested. get_class_methods returns only public methods
                continue;
                // @codeCoverageIgnoreEnd
            }

            $actual[$property] = $callable();
        }

        return $actual;
    }

    /**
     * Validates a Dto
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    private function validateRequest(RequestObjectInterface $instance): ConstraintViolationList
    {
        /** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $validator = $this->app->get(ValidatorInterface::class);

        // Pre-validate the object to match how the ValidatingMiddleware does it.

        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $validator->validate($instance, null, ['PreValidate']);
        if ($violations->count()) {
            return $violations;
        }

        // Validate with default and resolved validation groups.
        $groups = $instance->resolveValidationGroups();
        $groups[] = 'Default';

        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $validator->validate($instance, null, $groups);

        return $violations;
    }
}
