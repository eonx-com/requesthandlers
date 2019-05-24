<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Request;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

/**
 * @coversNothing
 */
class RequestObjectStub implements RequestObjectInterface
{
    /**
     * Test property
     *
     * @var string
     */
    private $property;

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

    /**
     * Returns test property
     *
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }
}
