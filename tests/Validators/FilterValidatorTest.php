<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Validators;

use LoyaltyCorp\RequestHandlers\Validators\Filter;
use LoyaltyCorp\RequestHandlers\Validators\FilterValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Translator\TranslatorStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Validator\ValidatorStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Validators\FilterValidator
 */
class FilterValidatorTest extends TestCase
{
    /**
     * Test invalid constraint passed
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "LoyaltyCorp\RequestHandlers\Validators\Filter", "Symfony\Component\Validator\Constraints\NotBlank" given'); // phpcs:ignore

        $validator = $this->getValidatorInstance();
        $validator->validate(null, new NotBlank());
    }

    /**
     * Test bails on empty value
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testEmptyValue(): void
    {
        $constraint = new Filter();

        $validator = $this->getValidatorInstance();
        $context = $this->buildContext($constraint);
        $validator->initialize($context);

        $validator->validate(null, $constraint);

        static::assertCount(0, $context->getViolations());
    }

    /**
     * Test filter int
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testFilterInt(): void
    {
        $constraint = new Filter(['filter' => 'FILTER_VALIDATE_INT']);

        $validator = $this->getValidatorInstance();
        $context = $this->buildContext($constraint);
        $validator->initialize($context);

        $validator->validate(5, $constraint);

        static::assertCount(0, $context->getViolations());
    }

    /**
     * Test filter int when it isnt
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testFilterStringInt(): void
    {
        $constraint = new Filter(['filter' => 'FILTER_VALIDATE_INT']);

        $validator = $this->getValidatorInstance();
        $context = $this->buildContext($constraint);
        $validator->initialize($context);

        $validator->validate('purple', $constraint);

        $violation = new ConstraintViolation(
            'This value is not valid.',
            'This value is not valid.',
            [],
            'root',
            '',
            'purple',
            null,
            null,
            $constraint
        );

        static::assertCount(1, $context->getViolations());
        static::assertEquals($violation, $context->getViolations()[0]);
    }

    /**
     * Test filter int when it isnt
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testFilterHexIntFlags(): void
    {
        $constraint = new Filter([
            'filter' => 'FILTER_VALIDATE_INT',
            'flags' => ['FILTER_FLAG_ALLOW_HEX']
        ]);

        $validator = $this->getValidatorInstance();
        $context = $this->buildContext($constraint);
        $validator->initialize($context);

        $validator->validate('0x5a', $constraint);

        static::assertCount(0, $context->getViolations());
    }

    /**
     * Builds a fake ExecutionContext
     *
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @return \Symfony\Component\Validator\Context\ExecutionContextInterface
     */
    private function buildContext(Constraint $constraint): ExecutionContextInterface
    {
        $validator = new ValidatorStub();
        $translator = new TranslatorStub();

        $context = new ExecutionContext(
            $validator,
            'root',
            $translator
        );

        $context->setConstraint($constraint);

        return $context;
    }

    /**
     * Get endpoint token validator instance
     *
     * @return \LoyaltyCorp\RequestHandlers\Validators\FilterValidator
     */
    private function getValidatorInstance(): FilterValidator
    {
        return new FilterValidator();
    }
}
