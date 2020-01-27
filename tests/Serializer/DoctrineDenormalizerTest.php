<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException;
use LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizer;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;
use stdClass;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer\DoctrineDenormalizerEntityFinderStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizer
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Required to test
 */
class DoctrineDenormalizerTest extends TestCase
{
    /**
     * Data for pass through failure tests.
     *
     * @return mixed[]
     */
    public function getPassThroughData(): iterable
    {
        yield 'property with value' => [
            'data' => ['code' => 'thing'],
            'type' => 'EntityClass'
        ];

        yield 'property with null value' => [
            'data' => ['code' => null],
            'type' => 'EntityClass'
        ];

        yield 'different type property with null value' => [
            'data' => ['code' => null],
            'type' => 'UnknownEntityClass'
        ];

        yield 'no mapped properties' => [
            'data' => ['unmapped' => null],
            'type' => 'EntityClass'
        ];
    }

    /**
     * Tests denormalize returns the entity that was found.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalize(): void
    {
        $entity = new stdClass();
        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder($entity),
            $registry
        );

        $result = $denormalizer->denormalize(['id' => 'entityId'], 'EntityClass');

        self::assertSame($entity, $result);
    }

    /**
     * Tests denormalize ints as ID fields.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeIntsMatchingId(): void
    {
        $entity = new stdClass();

        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder($entity),
            $registry
        );

        $result = $denormalizer->denormalize(789, 'EntityClass');

        self::assertSame($entity, $result);
    }

    /**
     * Tests that when denormalize is passed null it will return a null.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeNull(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder(),
            $registry
        );

        $result = $denormalizer->denormalize(null, 'EntityClass');

        self::assertNull($result);
    }

    /**
     * Tests denormalize passes the resolved object straight through if it already received
     * that object.
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

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder(),
            $registry
        );

        $result = $denormalizer->denormalize($entity, 'stdClass');

        self::assertSame($entity, $result);
    }

    /**
     * Tests denormalize with provided class-key mapping.
     *
     * @param mixed $data
     * @param string $type
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     *
     * @dataProvider getPassThroughData
     */
    public function testDenormalizePassThroughs($data, string $type): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder(),
            $registry,
            [
                'EntityClass' => ['code' => 'code', 'skip' => 'skip']
            ]
        );

        $result = $denormalizer->denormalize($data, $type);

        self::assertSame($data, $result);
    }

    /**
     * Tests denormalize passes context to the entity finder.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizePassesContext(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $finder = new DoctrineDenormalizerEntityFinderStub();
        $denormalizer = new DoctrineDenormalizer($finder, $registry);

        $expected = [
            'class' => 'EntityClass',
            'criteria' => [
                'externalId' => 'entityId'
            ],
            'context' => ['context' => 'array']
        ];

        $denormalizer->denormalize(['id' => 'entityId'], 'EntityClass', 'json', ['context' => 'array']);

        static::assertSame([$expected], $finder->getCalls());
    }

    /**
     * Tests denormalize scalar that doesn't match anything.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeScalarNonMatch(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer($this->createEntityFinder(), $registry);

        $result = $denormalizer->denormalize('purple', 'EntityClass');

        self::assertSame('purple', $result);
    }

    /**
     * Tests denormalize passes context to the entity finder when a scalar value is passed.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeScalarPassesContext(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $finder = new DoctrineDenormalizerEntityFinderStub();
        $denormalizer = new DoctrineDenormalizer($finder, $registry);

        $expected = [
            'class' => 'EntityClass',
            'criteria' => [
                'externalId' => 'entityId'
            ],
            'context' => ['context' => 'array']
        ];

        $denormalizer->denormalize('entityId', 'EntityClass', 'json', ['context' => 'array']);

        static::assertSame([$expected], $finder->getCalls());
    }

    /**
     * Tests denormalize strings as ID fields.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeStringMatchingId(): void
    {
        $entity = new stdClass();

        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder($entity),
            $registry
        );

        $result = $denormalizer->denormalize('entityId', 'EntityClass');

        self::assertSame($entity, $result);
    }

    /**
     * Tests denormalize strings as ID fields will uses the first custom field if defined.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeStringsWithCustomId(): void
    {
        $entity = new stdClass();

        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder($entity),
            $registry,
            ['EntityClass' => ['customId' => 'xxx']]
        );

        $result = $denormalizer->denormalize('entityIdValue', 'EntityClass');

        self::assertSame($entity, $result);
    }

    /**
     * Tests denormalize strings as ID fields will uses the first custom field if defined.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerMappingException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeStringsWithCustomIdNoMatch(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder(),
            $registry,
            ['EntityClass' => ['abc' => 'xxx', 'yyy' => 'zzz']]
        );

        $result = $denormalizer->denormalize('somevalue', 'EntityClass');

        self::assertSame('somevalue', $result);
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

        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer(
            $this->createEntityFinder($entity),
            $registry,
            [
                'EntityClass' => ['code' => 'code', 'skip' => 'skip']
            ]
        );

        $result = $denormalizer->denormalize(['code' => 'ABCDEFG'], 'EntityClass');

        self::assertSame($entity, $result);
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

        $denormalizer = new DoctrineDenormalizer($this->createEntityFinder(), $registry, [
            'EntityClass' => 'code'
        ]);

        $this->expectException(DoctrineDenormalizerMappingException::class);
        $this->expectExceptionMessage('Mis-configured class-key mappings in denormalizer');

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

        $denormalizer = new DoctrineDenormalizer($this->createEntityFinder(), $registry);

        self::assertTrue($denormalizer->supportsDenormalization([], 'EntityClass'));
        self::assertFalse($denormalizer->supportsDenormalization([], 'NotEntityClass'));
    }

    /**
     * Test that supports ignores the classes that have been set as to be ignored on the setup.
     *
     * @return void
     */
    public function testSupportsSkipsTheIgnoredClasses(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $denormalizer = new DoctrineDenormalizer($this->createEntityFinder(), $registry, null, ['CustomerClass']);

        $supports = $denormalizer->supportsDenormalization([
            'email' => 'example@example.com'
        ], 'CustomerClass');

        self::assertFalse($supports);
    }

    /**
     * Create entity finder instance
     *
     * @param object|null $entity The entity to return on find
     *
     * @return \LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface
     */
    private function createEntityFinder(?object $entity = null): DoctrineDenormalizerEntityFinderInterface
    {
        return new DoctrineDenormalizerEntityFinderStub($entity);
    }
}
