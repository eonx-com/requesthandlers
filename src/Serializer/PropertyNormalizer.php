<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BasePropertyNormalizer;

final class PropertyNormalizer extends BasePropertyNormalizer
{
    /**
     * Allows for the addition of extra properties to be set when denormalizing
     */
    public const EXTRA_PARAMETERS = 'extra_parameters';

    /**
     * {@inheritdoc}
     *
     * Adds functionality to denormalize to add additional properties configured as part of the context.
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $object = parent::denormalize($data, $class, $format, $context);

        /** @var mixed[] $extras */
        $extras = $context[self::EXTRA_PARAMETERS] ?? $this->defaultContext[self::EXTRA_PARAMETERS][$class] ?? [];

        foreach ($extras as $key => $value) {
            $this->setAttributeValue($object, $key, $value);
        }

        return $object;
    }

    /**
     * Overridden to add an attribute key to the context array. This allows us
     * to handle deserialization failures with DateTimes and DateIntervals and
     * add those messages to validation failures.
     *
     * {@inheritdoc}
     */
    protected function createChildContext(array $parentContext, $attribute): array
    {
        $context = parent::createChildContext(
            $parentContext,
            $attribute
        );

        $context['attribute'] = $this->nameConverter !== null
            ? $this->nameConverter->normalize($attribute)
            : $attribute;

        return $context;
    }
}
