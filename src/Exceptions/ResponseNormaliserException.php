<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\RuntimeException;

class ResponseNormaliserException extends RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 80;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }
}
