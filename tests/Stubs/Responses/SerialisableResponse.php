<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses;

use LoyaltyCorp\RequestHandlers\Response\AbstractSerialisableResponse;

/**
 * @coversNothing
 */
class SerialisableResponse extends AbstractSerialisableResponse
{
    /**
     * The purple elephants.
     *
     * @var string
     */
    private $purple = 'elephants';

    /**
     * Constructor
     *
     * @param int $statusCode
     */
    public function __construct(?int $statusCode)
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }
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
}
