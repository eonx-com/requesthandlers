<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\EventListeners;

use LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException;
use LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException;
use LoyaltyCorp\RequestHandlers\Request\Interfaces\ParamConverterManagerInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A custom ParamConverterListener that implements similar functionality to the
 * SensioFrameworkExtra bundle, but customised for our requirements.
 *
 * Specifically, the differences are:
 * - Use a custom ParamConverterManager that has an interface
 * - Do not support autoconfiguration, except for discovery of the types of parameters.
 */
class ParamConverterListener implements EventSubscriberInterface
{
    /**
     * @var \LoyaltyCorp\RequestHandlers\Request\Interfaces\ParamConverterManagerInterface
     */
    private $manager;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\RequestHandlers\Request\Interfaces\ParamConverterManagerInterface $manager
     */
    public function __construct(ParamConverterManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    /**
     * Listens for the onKernelController event to apply param converters to request
     * attributes.
     *
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     * @throws \ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $configurations = $this->getConfigurations($request);

        $this->configureTypes($configurations, $controller);

        $this->manager->apply($request, $configurations);
    }

    /**
     * Looks over all parameters of the controller callable and resolves types of
     * any configurations that do not have a type specified.
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter[] $configurations
     * @param callable $controller
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) Method is complicated.
     */
    private function configureTypes(array $configurations, callable $controller): void
    {
        $reflection = $this->getReflection($controller);

        foreach ($reflection->getParameters() as $parameter) {
            $class = $parameter->getClass();
            $hasType = $parameter->hasType();

            if ($class === null && $hasType === false) {
                // The parameter has no type defined so we cant use it to enrich.
                continue;
            }

            if (\array_key_exists($parameter->getName(), $configurations) === false) {
                // We dont have a configuration that matches the parameter.
                continue;
            }

            $configuration = $configurations[$parameter->getName()];

            if ($class !== null && $configuration->getClass() === null) {
                // Configuration does not have a class set, but we have one.
                $configuration->setClass($class->getName());
            }

            // If we have a scalar type that allows null
            $typeOptional = $hasType &&
                $parameter->getType() !== null &&
                $parameter->getType()->allowsNull();

            // If the parameter is optional or there is a default value
            $isOptional = $parameter->isOptional() ||
                $parameter->isDefaultValueAvailable();

            $configuration->setIsOptional($isOptional || $typeOptional);
        }
    }

    /**
     * Returns an array of ConfigurationInterface objects from the controller.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter[]
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidRequestAttributeException
     */
    private function getConfigurations(Request $request): array
    {
        $converters = $request->attributes->get('_converters');

        if (\is_array($converters) === false && $converters !== null) {
            throw new InvalidRequestAttributeException(
                'The _converters key did not contain an array of param converter configurations.'
            );
        }

        $configurations = [];

        foreach ((array)$converters as $configuration) {
            if (($configuration instanceof ParamConverter) === false) {
                continue;
            }

            /**
             * @var \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration
             *
             * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === chec
             */
            $configurations[$configuration->getName()] = $configuration;
        }

        return $configurations;
    }

    /**
     * Returns the reflection method for the controller callable.
     *
     * @param callable $controller
     *
     * @return \ReflectionFunctionAbstract
     *
     * @throws \ReflectionException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    private function getReflection(callable $controller): ReflectionFunctionAbstract
    {
        if (\is_array($controller)) {
            return new ReflectionMethod($controller[0], $controller[1]);
        }

        if (\is_object($controller) && \is_callable([$controller, '__invoke'])) {
            return new ReflectionMethod($controller, '__invoke');
        }

        if (\is_string($controller)) {
            return new ReflectionFunction($controller);
        }

        // @codeCoverageIgnoreStart
        // This shouldnt happen with the callable typehint, but is a protection incase it can.
        throw new ParamConverterMisconfiguredException('Provided callable isn\'t callable.');
        // @codeCoverageIgnoreEnd
    }
}
