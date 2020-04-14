<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer as BaseSerializer;

/**
 * Note: this serializer should only be used internally by the RequestBodyParamConverter -
 * it contains a custom feature that ignores normalisation errors, which isnt useful for
 * standard deserialisation.
 */
final class RequestBodySerializer extends BaseSerializer
{
    /**
     * Overridden to allow us to catch NotNormalizableValueExceptions instead of letting
     * the exception bubble.
     *
     * This lets us build an array of failures that can be added to the validation errors
     * ConstraintViolationList instead of bailing on deserialisation.
     *
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        try {
            return parent::denormalize($data, $type, $format, $context);
        } catch (NotNormalizableValueException $exception) {
            // When we receive a NotNormalizableValueException from an internal serialisation
            // event that marks the value as denormalizable we return it as is - the intention
            // for requesthandlers is that the validation phase will correctly validate that
            // the value matches an excepted type assertion.

            // Passing the value through means that we can continue denormalising without aborting
            // when we get weird data.
            return $data;
        }
    }
}
