<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request;

use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Request\RequestBodyParamConverter as BaseRequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class RequestBodyParamConverter extends BaseRequestBodyParamConverter
{
    /**
     * Stores the configuration so we can add some detail to the serializer
     * context.
     *
     * @var \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter|null
     */
    private $configuration;

    /**
     * Stores the request so we can add it to the serializer context.
     *
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    private $request;

    /**
     * Overridden so we can capture the $request and $configuration before the application
     * process, and add those details to the deserialisation context.
     *
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $this->configuration = $configuration;
        $this->request = $request;

        if ($request->getContentType() === null) {
            throw new InvalidContentTypeException(
                'Request has no content type when one is required for this Converter.'
            );
        }

        try {
            $result = parent::apply($request, $configuration);
        } catch (Exception $exception) {
            $this->configuration = null;
            $this->request = null;

            throw $exception;
        }

        $this->configuration = null;
        $this->request = null;

        return $result;
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
     * If we've got a request and a configuration, add default constructor arguments
     * based on the request's attributes. This allows us to inject anything that is type
     * hinted on the constructor to be added from the controller arguments.
     *
     * {@inheritdoc}
     */
    protected function configureContext(Context $context, array $options): void
    {
        $context->setAttribute(PropertyNormalizer::DISABLE_TYPE_ENFORCEMENT, true);

        if ($this->request !== null && $this->configuration !== null) {
            $context->setAttribute(
                PropertyNormalizer::EXTRA_PARAMETERS,
                $this->request->attributes->all()
            );
        }

        parent::configureContext($context, $options);
    }
}
