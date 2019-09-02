<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses;

use LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;

class ResponseSerialiserStub implements ResponseSerialiserInterface
{
    /**
     * @var mixed[]
     */
    private $result;

    /**
     * Constructor
     *
     * @param mixed[] $result
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function normalise(SerialisableResponseInterface $response): array
    {
        return $this->result;
    }
}
