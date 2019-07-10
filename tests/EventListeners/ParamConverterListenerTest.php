<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\EventListeners;

use LoyaltyCorp\RequestHandlers\Event\FilterControllerEvent;
use LoyaltyCorp\RequestHandlers\EventListeners\ParamConverterListener;
use LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Tests\LoyaltyCorp\RequestHandlers\EventListeners\Fixtures\TestController;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\ParamConverterManagerStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\EventListeners\ParamConverterListener
 */
class ParamConverterListenerTest extends TestCase
{
    /**
     * Tests the getSubscribedEvents method.
     *
     * @return void
     */
    public function testGetSubscribedEvents(): void
    {
        $expectedEvents = [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];

        static::assertSame($expectedEvents, ParamConverterListener::getSubscribedEvents());
    }

    /**
     * Tests the listener when _converters isnt an array.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerBadConverters(): void
    {
        $controller = new TestController();
        $request = new Request();
        $request->attributes->set('_converters', false);

        $event = new FilterControllerEvent(
            [$controller, 'method'],
            $request
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $this->expectException(InvalidRequestAttributeException::class);
        $this->expectExceptionMessage(
            'The _converters key did not contain an array of param converter configurations.'
        );

        $listener->onKernelController($event);
    }

    /**
     * Tests the listener
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerConverters(): void
    {
        $controller = new TestController();
        $paramConverter = new ParamConverter([]);
        $paramConverter->setName('parameter');

        $request = new Request();
        $request->attributes->set('_converters', [
            $paramConverter,
            new stdClass()
        ]);

        $expected = [
            'parameter' => $paramConverter
        ];

        $event = new FilterControllerEvent(
            [$controller, 'method'],
            $request
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $listener->onKernelController($event);

        static::assertSame($expected, $manager->getConfigurations());
        static::assertTrue($paramConverter->isOptional());
        static::assertSame(stdClass::class, $paramConverter->getClass());
    }

    /**
     * Tests the listener when no type information available
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerConvertersNoType(): void
    {
        $controller = new TestController();
        $paramConverter = new ParamConverter([]);
        $paramConverter->setName('untyped');

        $request = new Request();
        $request->attributes->set('_converters', [
            $paramConverter,
            new stdClass()
        ]);

        $event = new FilterControllerEvent(
            [$controller, 'untyped'],
            $request
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $listener->onKernelController($event);

        static::assertFalse($paramConverter->isOptional());
        static::assertNull($paramConverter->getClass());
    }

    /**
     * Tests the listener when no configuration is presented.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerNoConfiguration(): void
    {
        $controller = new TestController();
        $event = new FilterControllerEvent(
            [$controller, 'method'],
            new Request()
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $listener->onKernelController($event);

        // Nothing should happen.
        $this->addToAssertionCount(1);
    }

    /**
     * Tests the listener when no configuration is presented.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerNoConfigurationCallable(): void
    {
        $event = new FilterControllerEvent(
            'trim',
            new Request()
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $listener->onKernelController($event);

        // Nothing should happen.
        $this->addToAssertionCount(1);
    }

    /**
     * Tests the listener when no configuration is presented.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    public function testOnKernelControllerNoConfigurationInvokable(): void
    {
        $controller = new TestController();
        $event = new FilterControllerEvent(
            $controller,
            new Request()
        );

        $manager = new ParamConverterManagerStub();
        $listener = new ParamConverterListener($manager);

        $listener->onKernelController($event);

        // Nothing should happen.
        $this->addToAssertionCount(1);
    }
}
