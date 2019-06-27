<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\TestHelper\Fixtures;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TestRequest implements RequestObjectInterface
{
    /**
     * @Assert\Type("string")
     *
     * @var mixed
     */
    private $property;

    /**
     * Constructor
     *
     * @param mixed $property
     */
    public function __construct($property = null)
    {
        $this->property = $property;
    }

    /**
     * Returns the property
     *
     * @return mixed
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExceptionClass(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function resolveValidationGroups(): array
    {
        return [];
    }
}
