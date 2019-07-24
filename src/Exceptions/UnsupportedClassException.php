<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\RuntimeException;

class UnsupportedClassException extends RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 60;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }
}
