<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Builder\Interfaces;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;

interface ObjectValidatorInterface
{
    /**
     * The group used for pre validation.
     *
     * @const string
     */
    public const PREVALIDATE_GROUP = 'PreValidate';

    /**
     * Ensures that a Request Object is validated.
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $object
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException
     */
    public function ensureValidated(RequestObjectInterface $object): void;
}
