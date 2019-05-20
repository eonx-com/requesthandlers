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
class RequestBodySerializer extends BaseSerializer
{
    /**
     * @var \Symfony\Component\Serializer\Exception\NotNormalizableValueException[]
     */
    private $failures = [];

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
            // Instead of the default behaviour of the serializer, if we encounter a value
            // that cannot be normalised, set the value to null and capture the error.

            // This allows us to present the normalisation errors as part of a validation
            // failure exception.
            if (\array_key_exists('attribute', $context) === true) {
                $this->failures[$context['attribute']] = $exception;
            }

            return null;
        }
    }

    /**
     * @return \Symfony\Component\Serializer\Exception\NotNormalizableValueException[]
     */
    public function getFailures(): array
    {
        return $this->failures;
    }
}
