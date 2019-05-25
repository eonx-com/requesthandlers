<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Exceptions;

use EoneoPay\Utils\Exceptions\NotFoundException;
use Throwable;

class DoctrineParamConverterEntityNotFoundException extends NotFoundException
{
    /**
     * @var null|string
     */
    private $entityClass;

    /**
     * @var null|string
     */
    private $paramName;

    /**
     * Constructor
     *
     * @param null|string $message
     * @param mixed[]|null $messageParameters
     * @param null|string $entityClass
     * @param null|string $paramName
     * @param int|null $code
     * @param null|\Throwable $previous
     */
    public function __construct(
        ?string $message = null,
        ?array $messageParameters = null,
        ?string $entityClass = null,
        ?string $paramName = null,
        ?int $code = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $messageParameters, $code, $previous);

        $this->entityClass = $entityClass;
        $this->paramName = $paramName;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return 11;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return 1;
    }

    /**
     * Entity class that was not found.
     *
     * @return null|string
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * The parameter name that the entity was meant to be injected into.
     *
     * @return null|string
     */
    public function getParamName(): ?string
    {
        return $this->paramName;
    }
}
