<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Response;

use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class AbstractSerialisableResponse implements SerialisableResponseInterface
{
    /**
     * The HTTP Status code of the response.
     *
     * This property is intentionally prefixed with an underscore - it is an internal
     * implementation detail of this abstract class. The serialiser will not serialise
     * this response code.
     *
     * @var int
     */
    private $_statusCode; // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * Returns the HTTP status code of the response.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->_statusCode;
    }

    /**
     * Sets the status code to be returned by the response.
     *
     * @param int $statusCode
     *
     * @return void
     */
    protected function setStatusCode(int $statusCode): void
    {
        $this->_statusCode = $statusCode;
    }
}
