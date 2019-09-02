<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses;

use DateTime as BaseDateTime;
use DateTimeZone;
use EoneoPay\Utils\DateTime;
use LoyaltyCorp\RequestHandlers\Response\AbstractSerialisableResponse;

/**
 * @coversNothing
 */
class SerialisableResponse extends AbstractSerialisableResponse
{
    /**
     * @var \DateTime
     */
    private $localTime;

    /**
     * The purple elephants.
     *
     * @var string
     */
    private $purple = 'elephants';

    /**
     * @var \DateTime
     */
    private $utcTime;

    /**
     * Constructor
     *
     * @param int $statusCode
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function __construct(?int $statusCode)
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }

        $this->localTime = new DateTime('2019-01-02T03:04:05', new DateTimeZone('Australia/Melbourne'));
        $this->utcTime = new DateTime('2019-02-03T04:05:06', new DateTimeZone('UTC'));
    }

    /**
     * Returns a local datetime object.
     *
     * @return \DateTime
     */
    public function getLocalTime(): BaseDateTime
    {
        return $this->localTime;
    }

    /**
     * Returns the purple elephants.
     *
     * @return string
     */
    public function getPurple(): string
    {
        return $this->purple;
    }

    /**
     * Returns a utc datetime object.
     *
     * @return \DateTime
     */
    public function getUtcTime(): BaseDateTime
    {
        return $this->utcTime;
    }
}
