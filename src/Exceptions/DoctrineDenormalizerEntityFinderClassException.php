<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\BaseException;

class DoctrineDenormalizerEntityFinderClassException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 31;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return static::DEFAULT_STATUS_CODE_RUNTIME;
    }
}
