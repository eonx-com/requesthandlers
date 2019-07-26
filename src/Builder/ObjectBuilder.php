<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ObjectBuilder implements ObjectBuilderInterface
{
    /**
     * The group used for pre validation.
     */
    private const PREVALIDATE_GROUP = 'PreValidate';

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     */
    public function build(string $objectClass, string $json, ?array $context = null): RequestObjectInterface
    {
        $instance = $this->serializer->deserialize(
            $json,
            $objectClass,
            'json',
            [
                PropertyNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                PropertyNormalizer::EXTRA_PARAMETERS => $context ?? []
            ]
        );

        if (($instance instanceof $objectClass) === false) {
            throw new MisconfiguredSerializerException(\sprintf(
                'The serializer returned an object of type "%s" but it is not an instance of "%s"',
                \get_class($instance),
                $objectClass
            ));
        }

        if (($instance instanceof RequestObjectInterface) === false) {
            throw new UnsupportedClassException(\sprintf(
                'The supplied class "%s" is not supported. It must be an instance of "%s"',
                $objectClass,
                RequestObjectInterface::class
            ));
        }

        /**
         * @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === check
         */

        $this->ensureValidated($instance);

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException
     */
    public function buildWithContext(string $objectClass, array $context): RequestObjectInterface
    {
        return $this->build($objectClass, '{}', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function ensureValidated(RequestObjectInterface $instance): void
    {
        $violations = $this->validate($instance);

        if ($violations->count() === 0) {
            return;
        }

        $exceptionClass = $instance::getExceptionClass();

        throw new $exceptionClass($violations);
    }

    /**
     * Validates a request object.
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    private function validate(RequestObjectInterface $instance): ConstraintViolationList
    {
        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $this->validator->validate(
            $instance,
            null,
            [static::PREVALIDATE_GROUP]
        );

        if ($violations->count()) {
            return $violations;
        }

        // Validate with default and resolved validation groups.
        $groups = $instance->resolveValidationGroups();
        $groups[] = 'Default';

        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $this->validator->validate($instance, null, $groups);

        return $violations;
    }
}
