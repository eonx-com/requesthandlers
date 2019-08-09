<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException;
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
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     * @param mixed[]|null $classKeyMap
     * @param mixed[]|null $ignoreClasses
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?array $classKeyMap = null,
        ?array $ignoreClasses = null
    ) {
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
        if ($data instanceof $class === true) {
            // In some circumstances we're going to end up with $data being an object
            // already, especially when we're using ->denormalize() specifically instead
            // of deserialize

            return $data;
        }
        if (\is_string($data) === true) {
            $result = $this->findOneBy($class, ['externalId' => $data]);
            if ($result !== null) {
                return $result;
            }
        }
        if ($data === null || \is_array($data) === false) {
            // If the data is null or we didnt get an array, just return the data
            // so that the object can be validated with the user supplied data.

            return $data;
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

        return $this->findOneBy($class, $criteria);
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
     * Find an entity by given criteria.
     *
     * @param string $class Class name
     * @param mixed[] $criteria Criteria to find an entity
     *
     * @return object|null
     */
    private function findOneBy(string $class, array $criteria): ?object
    {
        return $this->managerRegistry->getRepository($class)
            ->findOneBy($criteria);
    }

    /**
     * Get lookup key for given class.
     *
     * @param string $class Class name
     *
     * @return mixed[]
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

        return \array_merge($default, $keyMap);
    }
}
