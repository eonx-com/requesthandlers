<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @coversNothing
 */
class ManagerRegistryStub implements ManagerRegistry
{
    /**
     * @var mixed|null
     */
    private $objectRepository;

    /**
     * Constructs a new instance of ManagerRegistryStub.
     *
     * @param mixed $objectRepository
     */
    public function __construct($objectRepository = null)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($name = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getManager($name = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.LongVariable) Parameter is inherited from interface
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->objectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
    {
    }
}
