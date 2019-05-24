<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request;

interface RequestObjectInterface
{
    /**
     * Returns the exception class to be used when a failure occurs trying to deserialise
     * and validate the request object.
     *
     * @return string
     */
    public static function getExceptionClass(): string;

    /**
     * Returns validation groups that should be used when validating this object.
     *
     * @return string[]
     */
    public function resolveValidationGroups(): array;
}
