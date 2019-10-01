<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\UnsupportedClassException;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class ObjectBuilder implements ObjectBuilderInterface
{
    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     * @param \LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface $validator
     */
    public function __construct(SerializerInterface $serializer, ObjectValidatorInterface $validator)
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

        $this->validator->ensureValidated($instance);

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
}
