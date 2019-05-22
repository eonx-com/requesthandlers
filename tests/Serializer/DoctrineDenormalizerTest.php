<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizer;
use stdClass;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizer
 */
class DoctrineDenormalizerTest extends TestCase
{
    /**
     * Tests denormalize
     *
     * @return void
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalize(): void
    {
        $entity = new stdClass();

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['externalId' => 'entityId'], $entity],
                [['externalId' => 'nope'], null]
            ]);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::exactly(2))
            ->method('getRepository')
            ->with('EntityClass')
            ->willReturn($repository);

        $denormalizer = new DoctrineDenormalizer($registry);
        $result = $denormalizer->denormalize(['id' => 'entityId'], 'EntityClass');
        self::assertSame($entity, $result);

        $result = $denormalizer->denormalize(['id' => 'nope'], 'EntityClass');
        self::assertNull($result);

        $result = $denormalizer->denormalize(['id' => null], 'EntityClass');
        self::assertNull($result);
    }

    /**
     * Tests denormalize
     *
     * @return void
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeObject(): void
    {
        $entity = new stdClass();

        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer($registry);
        $result = $denormalizer->denormalize($entity, 'stdClass');
        self::assertSame($entity, $result);
    }

    /**
     * Tests that supports works correctly.
     *
     * @return void
     */
    public function testSupports(): void
    {
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects(self::once())
            ->method('isTransient')
            ->willReturn(false);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::exactly(2))
            ->method('getManagerForClass')
            ->willReturnMap([
                ['EntityClass', $manager],
                ['NotEntityClass', null]
            ]);

        $denormalizer = new DoctrineDenormalizer($registry);

        self::assertTrue($denormalizer->supportsDenormalization([], 'EntityClass', null));
        self::assertFalse($denormalizer->supportsDenormalization([], 'NotEntityClass', null));
    }
}
