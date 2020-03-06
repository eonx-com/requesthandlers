<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Fixtures;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

/**
 * @DiscriminatorMap(mapping={
 *     "sub" = "Tests\LoyaltyCorp\RequestHandlers\Fixtures\SubDiscriminatedRequest"
 * }, typeProperty="type")
 */
class DiscriminatedRequest implements RequestObjectInterface
{
    /**
     * @Assert\Type("string")
     *
     * @var mixed
     */
    private $property;

    /**
     * @Assert\Type("string")
     *
     * @var string
     */
    private $type;

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
     * {@inheritdoc}
     */
    public static function getExceptionClass(): string
    {
        return RequestValidationExceptionStub::class;
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
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveValidationGroups(): array
    {
        return [];
    }
}
