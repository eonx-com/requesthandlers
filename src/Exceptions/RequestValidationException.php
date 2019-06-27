<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\BaseException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
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
     * This method converts symfony ConstraintViolation objects into a message array
     * format that closely follows the laravel validation errors format.
     *
     * @return mixed[]
     */
    public function getErrors(): array
    {
        $errors = [];

        $converter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($this->getViolations() as $violation) {
            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            $path = $converter->normalize($violation->getPropertyPath());
            $errors[$path] = $errors[$path] ?? [];

            $errors[$path][] = $violation->getMessage();
        }

        return $errors;
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
