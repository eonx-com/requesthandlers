<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use EoneoPay\Utils\Interfaces\AnnotationReaderInterface;
use LoyaltyCorp\RequestHandlers\Annotations\InjectedFromContext;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BasePropertyNormalizer;

final class PropertyNormalizer extends BasePropertyNormalizer
{
    /**
     * Allows for the addition of extra properties to be set when denormalizing
     */
    public const EXTRA_PARAMETERS = 'extra_parameters';

    /**
     * @var \EoneoPay\Utils\Interfaces\AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * Constructor
     *
     * @param \EoneoPay\Utils\Interfaces\AnnotationReaderInterface $annotationReader
     * @param null|\Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface $classMetadataFactory
     * @param null|\Symfony\Component\Serializer\NameConverter\NameConverterInterface $nameConverter
     * @param null|\Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface $propertyTypeExtractor
     * @param null|\Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface $classDiscriminatorResolver
     * @param callable|null $objectClassResolver
     * @param mixed[]|null $defaultContext
     *
     * @SuppressWarnings(PHPMD.LongVariable) Required from extended class
     */
    public function __construct(
        AnnotationReaderInterface $annotationReader,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?NameConverterInterface $nameConverter = null,
        ?PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        ?callable $objectClassResolver = null,
        ?array $defaultContext = null
    ) {
        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyTypeExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext ?? []
        );

        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     *
     * Adds functionality to denormalize to add additional properties configured as part of the context.
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $object = parent::denormalize($data, $class, $format, $context);

        /** @var mixed[] $extras */
        $extras = $context[self::EXTRA_PARAMETERS] ?? $this->defaultContext[self::EXTRA_PARAMETERS][$class] ?? [];

        foreach ($extras as $key => $value) {
            $this->setAttributeValue($object, $key, $value);
        }

        return $object;
    }

    /**
     * Overridden to add an attribute key to the context array. This allows us
     * to handle deserialization failures with DateTimes and DateIntervals and
     * add those messages to validation failures.
     *
     * {@inheritdoc}
     */
    protected function createChildContext(array $parentContext, $attribute): array
    {
        $context = parent::createChildContext(
            $parentContext,
            $attribute
        );

        $context['attribute'] = $this->nameConverter !== null
            ? $this->nameConverter->normalize($attribute)
            : $attribute;

        return $context;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to allow InjectFromContext to block request data from making it into object
     * properties annotated as only being injectable from context.
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = []): bool
    {
        $class = \is_string($classOrObject) === false
            ? \get_class($classOrObject)
            : $classOrObject;

        $injected = $this->annotationReader->getClassPropertyAnnotation($class, InjectedFromContext::class);

        // If we found an InjectedFromContext annotation on the attribute we do not want to allow
        // the attribute to be set by the serialiser. It can still be set if provided by the
        // EXTRA_PARAMETERS functionality of this class.
        if ((($injected[$attribute] ?? null) instanceof InjectedFromContext) === true) {
            return false;
        }

        return parent::isAllowedAttribute(
            $classOrObject,
            $attribute,
            $format,
            $context
        );
    }
}
