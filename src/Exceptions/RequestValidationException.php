<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\BaseException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

abstract class RequestValidationException extends BaseException
{
    /**
     * Validation errors that have occurred.
     *
     * @var \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private $violations;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @param null|string $message
     * @param mixed[]|null $messageParameters
     * @param int|null $code
     * @param null|\Throwable $previous
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        ?string $message = null,
        ?array $messageParameters = null,
        ?int $code = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $messageParameters, $code, $previous);

        $this->violations = $violations;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return static::DEFAULT_STATUS_CODE_VALIDATION;
    }

    /**
     * Returns the violations that have occurred that caused this exception.
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
