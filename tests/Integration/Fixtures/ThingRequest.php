<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures;

use EoneoPay\Utils\DateTime;
use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;
use LoyaltyCorp\RequestHandlers\Validators\Filter;
use Symfony\Component\Validator\Constraints as Assert;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;

class ThingRequest implements RequestObjectInterface
{
    /**
     * A not nullable string amount.
     *
     * @Assert\NotNull()
     * @Assert\GreaterThan(1)
     * @Assert\Type("numeric")
     *
     * @var string|float|int
     */
    private $amount;

    /**
     * Property for importing via route attributes.
     *
     * @Assert\Type("string")
     *
     * @var string
     */
    private $baz;

    /**
     * Not nullable string date
     *
     * @Assert\NotNull()
     * @Assert\DateTime(format="Y-m-d\TH:i:sP")
     * @Assert\Type("string")
     *
     * @var string
     */
    private $date;

    /**
     * Float
     *
     * @Assert\NotNull
     * @Assert\GreaterThan(0.5)
     * @Assert\Type("numeric")
     *
     * @var float|int|string
     */
    private $float;

    /**
     * Integer
     *
     * @Assert\LessThan(0)
     * @Assert\NotNull()
     *
     * @Filter("FILTER_VALIDATE_INT")
     *
     * @var int|string
     */
    private $int;

    /**
     * A not nullable string
     *
     * @Assert\Length(min=2)
     * @Assert\NotNull()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $string;

    /**
     * {@inheritdoc}
     */
    public static function getExceptionClass(): string
    {
        return RequestValidationExceptionStub::class;
    }

    /**
     * Returns amount
     *
     * @return string
     */
    public function getAmount(): string
    {
        return (string)$this->amount;
    }

    /**
     * Returns the baz
     *
     * @return string
     */
    public function getBaz(): string
    {
        return $this->baz;
    }

    /**
     * Returns date
     *
     * @return \EoneoPay\Utils\DateTime
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function getDate(): DateTime
    {
        return new DateTime($this->date);
    }

    /**
     * Returns float
     *
     * @return float
     */
    public function getFloat(): float
    {
        return (float)$this->float;
    }

    /**
     * Returns int
     *
     * @return int
     */
    public function getInt(): int
    {
        return (int)$this->int;
    }

    /**
     * Returns string
     *
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveValidationGroups(): array
    {
        return [];
    }

    /**
     * Array used for testing assertions.
     *
     * @return mixed[]
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'date' => $this->getDate(),
            'float' => $this->getFloat(),
            'int' => $this->getInt(),
            'string' => $this->getString()
        ];
    }
}
