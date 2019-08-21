<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Fixtures;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

class TestBooleanRequest implements RequestObjectInterface
{
    /**
     * @Assert\Type("bool")
     *
     * @var bool
     */
    private $boolProperty;

    /**
     * TestRequest constructor.
     * @param bool $boolProperty
     */
    public function __construct(bool $boolProperty)
    {
        $this->boolProperty = $boolProperty;
    }

    /**
     * Returns the boolean property.
     *
     * @return bool
     */
    public function isBoolProperty(): bool
    {
        return $this->boolProperty;
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
