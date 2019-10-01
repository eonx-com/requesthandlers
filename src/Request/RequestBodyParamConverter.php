<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request;

use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Request\RequestBodyParamConverter as BaseRequestBodyParamConverter;
use FOS\RestBundle\Serializer\Serializer;
use LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException;
use LoyaltyCorp\RequestHandlers\Request\Interfaces\ContextConfiguratorInterface;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestBodyParamConverter extends BaseRequestBodyParamConverter
{
    /**
     * @var \LoyaltyCorp\RequestHandlers\Request\Interfaces\ContextConfiguratorInterface|null
     */
    private $contextConfigurator;

    /**
     * Stores the request so we can add it to the serializer context.
     *
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private $request;

    /**
     * Constructor
     *
     * @param \FOS\RestBundle\Serializer\Serializer $serializer
     * @param \LoyaltyCorp\RequestHandlers\Request\Interfaces\ContextConfiguratorInterface|null $contextConfigurator
     * @param string[]|null $groups
     * @param null|string $version
     * @param null|\Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(
        Serializer $serializer,
        ?ContextConfiguratorInterface $contextConfigurator = null,
        ?array $groups = null,
        ?string $version = null,
        ?ValidatorInterface $validator = null
    ) {
        $this->contextConfigurator = $contextConfigurator;

        parent::__construct(
            $serializer,
            $groups,
            $version,
            $validator
        );
    }

    /**
     * Overridden so we can capture the $request and $configuration before the application
     * process, and add those details to the deserialisation context.
     *
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $this->request = $request;

        if ($request->getContentType() === null) {
            throw new InvalidContentTypeException(
                'Request has no content type when one is required for this Converter.'
            );
        }

        try {
            return parent::apply($request, $configuration);
        } catch (Exception $exception) {
            throw $exception;
        } finally { // @codeCoverageIgnore
            $this->request = null;
        }
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * We're only going to support Dtos that inherit from our RequestDtoInterface.
     *
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() !== null &&
            \in_array(
                RequestObjectInterface::class,
                \class_implements($configuration->getClass()),
                true
            );
    }

    /**
     * Overrides the context configuration to add specific behaviours for request
     * deserialisation.
     *
     * {@inheritdoc}
     */
    protected function configureContext(Context $context, array $options): void
    {
        // Disable enforcement of types inside the serialiser. This may lead to invalid objects
        // that have bad data types for their properties, but this is intentional - it allows us
        // to then validate the objects with a Validator with the data provided instead of nulls
        // or weird serialiser exceptions.
        $context->setAttribute(PropertyNormalizer::DISABLE_TYPE_ENFORCEMENT, true);

        $attributes = [];
        if ($this->request instanceof Request === true) {
            $attributes = $this->request->attributes->all();

            if ($this->contextConfigurator instanceof ContextConfiguratorInterface === true) {
                // If we have a configurator, allow it to modify the context
                $this->contextConfigurator->configure($context, $this->request);
            }
        }

        // Set attributes in the deserialisation to all request attributes if a request exists.
        $context->setAttribute(PropertyNormalizer::EXTRA_PARAMETERS, $attributes);

        parent::configureContext($context, $options);
    }
}
