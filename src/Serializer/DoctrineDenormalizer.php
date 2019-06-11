<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * A denormalizer that accepts an array for a typed managed entity class
 * and looks up the id key of that array and returns an entity if it exists.
 */
class DoctrineDenormalizer implements DenormalizerInterface
{
    /**
     * Class-key mapping for finding entity.
     *
     * @var mixed[]|null
     */
    private $classKeyMap;

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     * @param mixed[]|null $classKeyMap
     */
    public function __construct(ManagerRegistry $managerRegistry, ?array $classKeyMap = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->classKeyMap = $classKeyMap;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, ?array $context = null)
    {
        if ($data instanceof $class === true) {
            // In some circumstances we're going to end up with $data being an object
            // already, especially when we're using ->denormalize() specifically instead
            // of deserialize

            return $data;
        }

        // Find lookup key for given class
        $findKey = $this->getClassLookupKey($class);

        // If a lookup key exists, then find by lookup key
        if ($findKey !== null && isset($data[$findKey]) === true) {
            return $this->findOneBy($class, [$findKey => $data[$findKey]]);
        }

        if (($data['id'] ?? null) === null) {
            return null;
        }

        // Default, lookup by externalId/id
        return $this->findOneBy($class, ['externalId' => $data['id']]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
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
     * @return mixed|null
     */
    private function getClassLookupKey(string $class)
    {
        if ($this->classKeyMap === null) {
            return null;
        }

        if (\array_key_exists($class, $this->classKeyMap) !== true) {
            return null;
        }

        return $this->classKeyMap[$class];
    }
}
