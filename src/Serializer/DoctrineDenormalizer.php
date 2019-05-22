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
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    /**
     * Constructor
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
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
}
