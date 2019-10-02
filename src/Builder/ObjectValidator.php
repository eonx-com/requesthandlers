<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Builder;

use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectValidatorInterface;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ObjectValidator implements ObjectValidatorInterface
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function ensureValidated(RequestObjectInterface $instance): void
    {
        $violations = $this->validate($instance);

        if ($violations->count() === 0) {
            return;
        }

        $exceptionClass = $instance::getExceptionClass();

        throw new $exceptionClass($violations);
    }

    /**
     * Validates a request object.
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface $instance
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    private function validate(RequestObjectInterface $instance): ConstraintViolationList
    {
        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $this->validator->validate(
            $instance,
            null,
            [static::PREVALIDATE_GROUP]
        );

        if ($violations->count()) {
            return $violations;
        }

        // Validate with default and resolved validation groups.
        $groups = $instance->resolveValidationGroups();
        $groups[] = 'Default';

        /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
        $violations = $this->validator->validate($instance, null, $groups);

        return $violations;
    }
}
