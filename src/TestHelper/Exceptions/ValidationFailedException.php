<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\TestHelper\Exceptions;

use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;

class ValidationFailedException extends RequestValidationException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 42;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }
}
