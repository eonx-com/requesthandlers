<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\RuntimeException;

class InvalidContentTypeException extends RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 40;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }
}
