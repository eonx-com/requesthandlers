<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer;

use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;

/**
 * @coversNothing
 */
class DoctrineDenormalizerEntityFinderStub implements DoctrineDenormalizerEntityFinderInterface
{
    /**
     * @var object|null
     */
    private $entity;

    /**
     * Constructor
     *
     * @param object|null $entity
     */
    public function __construct(?object $entity = null)
    {
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(string $class, array $criteria): ?object
    {
        return $this->entity;
    }
}
