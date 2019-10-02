<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;

class ObjectBuilderStub implements ObjectBuilderInterface
{
    /**
     * Objects to return when build is called.
     *
     * @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface[]
     */
    private $objects;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface[]|null $objects
     */
    public function __construct(?array $objects = null)
    {
        $this->objects = $objects ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $objectClass, string $json, ?array $context = null): RequestObjectInterface
    {
        return \array_shift($this->objects) ?: new $objectClass();
    }

    /**
     * {@inheritdoc}
     */
    public function buildWithContext(string $objectClass, array $context): RequestObjectInterface
    {
        return $this->build($objectClass, '{}', $context);
    }
}
