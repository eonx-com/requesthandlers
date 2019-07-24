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
