<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface;
use LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;

/**
 * @coversNothing
 */
class ObjectValidatorStub implements ObjectValidatorInterface
{
    /**
     * @var \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException|null
     */
    private $exception;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Exceptions\RequestValidationException|null $exception
     */
    public function __construct(?RequestValidationException $exception = null)
    {
        $this->exception = $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function ensureValidated(RequestObjectInterface $object): void
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
    }
}
