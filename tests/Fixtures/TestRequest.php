<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Fixtures;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

class TestRequest implements RequestObjectInterface
{
    /**
     * @Assert\Type("bool")
     *
     * @var bool|null
     */
    private $active;

    /**
     * @Assert\Type("string")
     *
     * @var mixed
     */
    private $property;

    /**
     * @Assert\Type("string")
     *
     * @var mixed
     */
    private $anotherProperty;

    /**
     * @Assert\Type("bool")
     *
     * @var bool|null
     */
    private $oneTime;

    /**
     * Constructor
     *
     * @param bool|null $active
     * @param bool|null $oneTime
     * @param mixed $property
     * @param mixed $anotherProperty
     */
    public function __construct(
        ?bool $active = null,
        ?bool $oneTime = null,
        $property = null,
        $anotherProperty = null
    ) {
        $this->active = $active;
        $this->oneTime = $oneTime;
        $this->property = $property;
        $this->anotherProperty = $anotherProperty;
    }

    /**
     * Returns the bool value of this method
     *
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * Returns the bool value of this method
     *
     * @return bool|null
     */
    public function isOneTime(): ?bool
    {
        return $this->oneTime;
    }

    /**
     * Returns the property
     *
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Returns another property
     *
     * @return mixed
     */
    public function getAnotherProperty()
    {
        return $this->anotherProperty;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExceptionClass(): string
    {
        return RequestValidationExceptionStub::class;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveValidationGroups(): array
    {
        return [];
    }
}
