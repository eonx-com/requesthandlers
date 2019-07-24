<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

class ObjectBuilderStub implements ObjectBuilderInterface
{
    /**
     * Objects to return when build is called.
     *
     * @var \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface[]
     */
    private $objects;

    /**
     * If the objects passed to ensure are valid.
     *
     * @var bool[]
     */
    private $validated;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface[]|null $objects
     * @param bool[]|null $validated
     */
    public function __construct(?array $objects = null, ?array $validated = null)
    {
        $this->objects = $objects ?? [];
        $this->validated = $validated ?? [];
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
    public function ensureValidated(RequestObjectInterface $object): void
    {
        $next = \array_shift($this->validated);

        if ($next === true) {
            return;
        }

        throw new RequestValidationExceptionStub(new ConstraintViolationList());
    }
}
