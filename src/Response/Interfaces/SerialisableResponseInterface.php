<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Response\Interfaces;

interface SerialisableResponseInterface
{
    /**
     * Returns the HTTP status code for the response.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int;
}
