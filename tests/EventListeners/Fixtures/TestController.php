<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\EventListeners\Fixtures;

use stdClass;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TestController
{
    /**
     * Invokable callable.
     *
     * @return void
     */
    public function __invoke(): void
    {
    }

    /**
     * A callable with no configuration.
     *
     * @param \stdClass $parameter
     *
     * @return void
     */
    public function method(?stdClass $parameter): void
    {
    }

    /**
     * Mixed type parameter.
     *
     * @param mixed $parameter
     *
     * @return void
     */
    public function untyped($parameter): void
    {
    }
}
