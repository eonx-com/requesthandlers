<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BasePropertyNormalizer;

class PropertyNormalizer extends BasePropertyNormalizer
{
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
