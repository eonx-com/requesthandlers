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
    private $findKeyMap;

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    /**
     * Constructor
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry, ?array $findKeyMap = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->findKeyMap = $findKeyMap;
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

        // find key from provided mapping
        $findKey = $this->getFindByKey($class);

        if ($findKey !== null) {
            return $this->managerRegistry->getRepository($class)
                ->findOneBy([$findKey => $data[$findKey]]);
        }

        if (($data['id'] ?? null) === null) {
            return null;
        }

        return $this->managerRegistry->getRepository($class)
            ->findOneBy(['externalId' => $data['id']]);
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
     * Get find by key from provided mappings.
     *
     * @param $class
     *
     * @return bool|null
     */
    public function getFindByKey($class): ?string
    {
        if ($this->findKeyMap === null) {
            return null;
        }

        if (\array_key_exists($class, $this->findKeyMap) !== true) {
            return null;
        }

        return $this->findKeyMap[$class];
    }
}
