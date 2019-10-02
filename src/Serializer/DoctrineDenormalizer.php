<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * A denormalizer that accepts an array for a typed managed entity class
 * and looks up the id key of that array and returns an entity if it exists.
 */
final class DoctrineDenormalizer implements DenormalizerInterface
{
    /**
     * Class-key mapping for finding entity.
     *
     * @var mixed[]|null
     */
    private $classKeyMap;

    /**
     * List of classes this normalizer does not handle.
     *
     * @var mixed[]
     */
    private $ignoreClasses;

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var \LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface
     */
    private $entityFinder;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface $entityFinder
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     * @param mixed[]|null $classKeyMap
     * @param mixed[]|null $ignoreClasses
     */
    public function __construct(
        DoctrineDenormalizerEntityFinderInterface $entityFinder,
        ManagerRegistry $managerRegistry,
        ?array $classKeyMap = null,
        ?array $ignoreClasses = null
    ) {
        $this->entityFinder = $entityFinder;
        $this->managerRegistry = $managerRegistry;
        $this->classKeyMap = $classKeyMap;
        $this->ignoreClasses = $ignoreClasses ?? [];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     */
    public function denormalize($data, $class, $format = null, ?array $context = null)
    {
        // If the data isn't an array, hand off to see if we can still discover the entity from it.
        if (\is_array($data) === false) {
            return $this->denormalizeNonArray($data, $class);
        }

        // entity criteria
        $criteria = [];

        // Find lookup key for given class
        $findKeys = $this->getClassLookupKey($class);

        foreach ($findKeys as $entityKey => $requestKey) {
            if (\array_key_exists($requestKey, $data) === true &&
                $data[$requestKey] !== null) {
                $criteria[$entityKey] = $data[$requestKey];
            }
        }

        // if criteria is empty i.e. no request key found for this class then return null
        if (\count($criteria) === 0) {
            return null;
        }

        return $this->entityFinder->findOneBy($class, $criteria, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        if (\in_array($type, $this->ignoreClasses, true) === true) {
            // Ignoring this class as part of setup denormalizer has.
            return false;
        }

        $manager = $this->managerRegistry->getManagerForClass($type);

        if ($manager === null) {
            return false;
        }

        return $manager->getMetadataFactory()->isTransient($type) === false;
    }

    /**
     * Attempts to denormalize a string or integer into an Entity
     *
     * @param mixed  $data    Data to restore
     * @param string $class   The expected class to instantiate
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     */
    private function denormalizeNonArray($data, string $class)
    {
        if (\is_string($data) !== true && \is_int($data) !== true) {
            return $data;
        }
        $keys = $this->getClassLookupKey($class);
        // If there's more than a single key, and the defined default, we've got a composite key, which we can't
        // handle with a single scalar value.
        if (\count($keys) > 2) {
            return $data;
        }
        $key = \array_key_first($keys);
        $result = $this->entityFinder->findOneBy($class, [$key => $data]);
        return $result ?? $data;
    }

    /**
     * Get lookup key for given class.
     *
     * @param string $class Class name
     *
     * @return string[] Array where key is the Entity ID field, value is the key in the JSON request.
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     */
    private function getClassLookupKey(string $class): array
    {
        $default = ['externalId' => 'id'];

        if ($this->classKeyMap === null ||
            \array_key_exists($class, $this->classKeyMap) !== true) {
            // return array [entity key => request key]
            return $default;
        }

        $keyMap = $this->classKeyMap[$class];

        // if the key map is not an array, return default
        if (\is_array($keyMap) !== true) {
            throw new DoctrineDenormalizerMappingException('Mis-configured class-key mappings in denormalizer.');
        }

        return \array_merge($keyMap, $default);
    }
}
