<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Response\Interfaces;

interface ResponseSerialiserInterface
{
    /**
     * Performs a normalisation on the response if one can be performed.
     *
     * @param \LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface $response
     *
     * @return mixed[]
     */
    public function normalise(SerialisableResponseInterface $response): array;
}
