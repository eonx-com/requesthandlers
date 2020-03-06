<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use EoneoPay\Utils\Interfaces\AnnotationReaderInterface;
use LoyaltyCorp\RequestHandlers\Annotations\InjectedFromContext;
use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
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
     * @var \Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface|null
     */
    private $classResolver;

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

        // Undo the classDiscriminatorResolver of the underlying ObjectNormalizer
        // so we can handle class resolution here.
        $this->classResolver = $this->classDiscriminatorResolver;
        $this->classDiscriminatorResolver = null;
    }

    /**
     * {@inheritdoc}
     *
     * Adds functionality to denormalize to add additional properties configured as part of the context.
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $object = parent::denormalize($data, $type, $format, $context);

        // We didnt get an object, but this doesnt actually happen in real life, just
        // because of the return type of denormalize.
        if (\is_array($object) === true) {
            return $object; // @codeCoverageIgnore
        }

        /** @var mixed[] $extras */
        $extras = $context[self::EXTRA_PARAMETERS] ?? $this->defaultContext[self::EXTRA_PARAMETERS][$type] ?? [];

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
     * Overridden so we support a more flexible class discriminator resolution than
     * the default symfony system.
     *
     * If we have a class discriminator, the type property isn't present or isn't a
     * valid value and the annotated base type isn't abstract, just use the base
     * type so that our validator can do its job.
     *
     * {@inheritdoc}
     *
     * @throws \ReflectionException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function instantiateObject(
        array &$data,
        $class,
        array &$context,
        \ReflectionClass $reflectionClass,
        $allowedAttributes,
        ?string $format = null
    ) {
        $class = $this->resolveClassMapping($class, $data);
        /** @noinspection SuspiciousAssignmentsInspection */
        $reflectionClass = new ReflectionClass($class);

        return parent::instantiateObject(
            $data,
            $class,
            $context,
            $reflectionClass,
            $allowedAttributes,
            $format
        );
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

    /**
     * Resolves the class mapping for a class that has a discriminator. If the
     * type property is missing or contains an invalid value the method returns
     * the original class.
     *
     * This is done for Eonx purposes so we can still receive an object from the
     * serialiser and validate it - it requires that a discriminated object must
     * not be abstract.
     *
     * @param string $class
     * @param mixed[] $data
     *
     * @return string
     */
    private function resolveClassMapping(string $class, array &$data): string
    {
        if ($this->classResolver instanceof ClassDiscriminatorResolverInterface === false) {
            return $class;
        }

        $mapping = $this->classResolver->getMappingForClass($class);

        // The class isnt a discriminator mapped class.
        if ($mapping instanceof ClassDiscriminatorMapping === false ||
            isset($data[$mapping->getTypeProperty()]) === false) {
            return $class;
        }

        $type = $data[$mapping->getTypeProperty()];

        return $mapping->getClassForType($type) ?? $class;
    }
}
