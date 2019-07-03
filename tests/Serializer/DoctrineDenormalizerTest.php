<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException;
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
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
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
     * Tests denormalize null
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeNull(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $denormalizer = new DoctrineDenormalizer($registry);

        $result = $denormalizer->denormalize(null, 'EntityClass');

        self::assertNull($result);
    }

    /**
     * Tests denormalize scalar
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeScalar(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $denormalizer = new DoctrineDenormalizer($registry);

        $result = $denormalizer->denormalize('purple', 'EntityClass');

        self::assertSame('purple', $result);
    }

    /**
     * Tests denormalize
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
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
     * Tests denormalize with provided class-key mapping.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeWithGivenMapping(): void
    {
        $entity = new stdClass();

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['code' => 'ABCDEFG'], $entity],
                [['code' => 'invalid'], null]
            ]);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::exactly(2))
            ->method('getRepository')
            ->with('EntityClass')
            ->willReturn($repository);

        $denormalizer = new DoctrineDenormalizer($registry, [
            'EntityClass' => ['code' => 'code', 'skip' => 'skip']
        ]);

        $result = $denormalizer->denormalize(['code' => 'ABCDEFG'], 'EntityClass');
        self::assertSame($entity, $result);

        $result = $denormalizer->denormalize(['code' => 'invalid'], 'EntityClass');
        self::assertNull($result);

        $result = $denormalizer->denormalize(['code' => null], 'EntityClass');
        self::assertNull($result);

        $result = $denormalizer->denormalize(['code' => null], 'UnknownEntityClass');
        self::assertNull($result);

        $result = $denormalizer->denormalize(['unmapped' => null], 'EntityClass');
        self::assertNull($result);
    }

    /**
     * Test that incorrect DoctrineDenormalizer class-key mapping will throw DoctrineDenormalizerMappingException.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeWithGivenMappingThrowsDoctrineDenormalizerMappingException(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $this->expectException(DoctrineDenormalizerMappingException::class);
        $this->expectExceptionMessage('Mis-configured class-key mappings in denormalizer');

        $denormalizer = new DoctrineDenormalizer($registry, [
            'EntityClass' => 'code'
        ]);

        $denormalizer->denormalize(['code' => 'ABCDEFG'], 'EntityClass');
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
