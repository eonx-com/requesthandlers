<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer\Interfaces;

interface DoctrineDenormalizerEntityFinderInterface
{
    /**
     * Find an entity by given criteria.
     *
     * @param string $class Class name
     * @param mixed[] $criteria Criteria to find an entity
     * @param mixed[]|null $context
     *
     * @return object|null
     */
    public function findOneBy(
        string $class,
        array $criteria,
        ?array $context = null
    ): ?object;
}
