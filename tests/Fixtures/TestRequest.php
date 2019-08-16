<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Fixtures;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

class TestRequest implements RequestObjectInterface
{
    /**
     * @Assert\Type("string")
     *
     * @var mixed
     */
    private $property;

    /**
     * @Assert\Type("bool")
     *
     * @var bool|null
     */
    private $oneTime;

    /**
     * Constructor
     *
     * @param bool|null $oneTime
     * @param mixed $property
     */
    public function __construct(?bool $oneTime = null, $property = null)
    {
        $this->oneTime = $oneTime;
        $this->property = $property;
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
