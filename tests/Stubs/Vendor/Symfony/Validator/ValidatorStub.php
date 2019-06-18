<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversNothing
 */
class ValidatorStub implements ValidatorInterface
{
    /**
     * @var \Symfony\Component\Validator\ConstraintViolation[][]
     */
    private $violations;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Validator\ConstraintViolation[][]|null $violations
     */
    public function __construct(?array $violations = null)
    {
        $this->violations = $violations ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function inContext(ExecutionContextInterface $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startContext()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraints = null, $groups = null)
    {
        return new ConstraintViolationList(\array_shift($this->violations) ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function validateProperty($object, $propertyName, $groups = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validatePropertyValue($objectOrClass, $propertyName, $value, $groups = null)
    {
    }
}
