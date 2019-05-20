<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer;

use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;

/**
 * @coversNothing
 */
class PropertyNormalizerStub extends PropertyNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function createChildContext(array $parentContext, $attribute): array
    {
        return parent::createChildContext($parentContext, $attribute);
    }
}
