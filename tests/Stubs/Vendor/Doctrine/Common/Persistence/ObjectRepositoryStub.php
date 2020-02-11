<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @coversNothing
 */
class ObjectRepositoryStub implements ObjectRepository
{
    /**
     * @var mixed[]
     */
    private $entities;

    /**
     * Constructs a new instance of ObjectRepositoryStub.
     *
     * @param mixed[]|null $entities
     */
    public function __construct(?array $entities = null)
    {
        $this->entities = $entities ?? [];
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ShortVariable) Parameter is inherited from interface
     */
    public function find($id): ?object
    {
        return \reset($this->entities);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria): ?object
    {
        $entity = \reset($this->entities);

        if ($entity === false) {
            return null;
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return self::class;
    }
}
