<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\TestHelper;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use LoyaltyCorp\RequestHandlers\TestHelper\Exceptions\ValidationFailedException;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @var \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface
     */
    private $objectBuilder;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface $objectBuilder
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(
        ObjectBuilderInterface $objectBuilder,
        SerializerInterface $serializer
    ) {
        $this->objectBuilder = $objectBuilder;
        $this->serializer = $serializer;
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
        /** @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance */
        $instance = $this->serializer->deserialize(
            $json,
            $class,
            'json',
            [
                PropertyNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
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
        try {
            return $this->objectBuilder->build($class, $json, $context);
        } catch (RequestValidationException $exception) {
            throw new ValidationFailedException(
                $exception->getViolations(),
                'Got validation failures when trying to build a validated request.',
                null,
                null,
                $exception
            );
        }
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
                return \strncmp($method, 'get', 3) === 0 ||
                    \strncmp($method, 'is', 2) === 0;
            }
        );

        $actual = [];

        //sorting methods alphabetically to return all the gets first then is.
        \asort($methodsToCheck);

        foreach ($methodsToCheck as $method) {
            $length = \strncmp($method, 'get', 3) === 0 ? 3 : 2;
            $property = \lcfirst(\substr($method, $length));
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
}
