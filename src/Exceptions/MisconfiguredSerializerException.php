<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\RuntimeException;

class MisconfiguredSerializerException extends RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 70;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }
}
