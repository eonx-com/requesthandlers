<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;

class ManagerRegistryStub implements ManagerRegistry
{
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
    public function getManagerNames()
    {
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
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
    {
    }
}
