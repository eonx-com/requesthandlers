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
     * @var mixed[]
     */
    private $calls;

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
    public function findOneBy(string $class, array $criteria, ?array $context = null): ?object
    {
        $this->calls[] = \compact('class', 'criteria', 'context');

        return $this->entity;
    }

    /**
     * Returns calls to findOneBy.
     *
     * @return mixed[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
