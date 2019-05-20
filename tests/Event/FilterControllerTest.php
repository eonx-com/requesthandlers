<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Event;

use LoyaltyCorp\RequestHandlers\Event\FilterControllerEvent;
use LoyaltyCorp\RequestHandlers\Exceptions\KernelNotAvailableException;
use Symfony\Component\HttpFoundation\Request;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Event\FilterControllerEvent
 */
class FilterControllerTest extends TestCase
{
    /**
     * Tests getKernel throws
     *
     * @return void
     */
    public function testGetKernelFails(): void
    {
        $this->expectException(KernelNotAvailableException::class);

        $event = new FilterControllerEvent(
            function (): void {
            },
            new Request()
        );
        $event->getKernel();
    }

    /**
     * Tests getRequest
     *
     * @return void
     */
    public function testGetRequest(): void
    {
        $request = new Request();
        $event = new FilterControllerEvent(
            function (): void {
            },
            $request
        );

        self::assertSame($request, $event->getRequest());
    }

    /**
     * Tests misc methods
     *
     * @return void
     */
    public function testMethods(): void
    {
        $request = new Request();
        $event = new FilterControllerEvent(
            function (): void {
            },
            $request
        );

        self::assertSame(1, $event->getRequestType());
        self::assertTrue($event->isMasterRequest());
    }
}
