<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException;
use LoyaltyCorp\RequestHandlers\Serializer\DoctrineDenormalizerEntityFinder;
use stdClass;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ManagerRegistryStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ObjectRepositoryStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class DoctrineDenormalizerEntityFinderTest extends TestCase
{
    /**
     * Tests that the 'findOneBy' successfully returns an entity when the correct repository instance is used.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException
     */
    public function testFindOneBySuccessful(): void
    {
        $entity = new stdClass();
        $repository = new ObjectRepositoryStub([$entity]);
        $registry = new ManagerRegistryStub($repository);
        $instance = new DoctrineDenormalizerEntityFinder($registry);

        $result = $instance->findOneBy(stdClass::class, []);

        self::assertSame($entity, $result);
    }

    /**
     * Tests that the 'findOneBy' method throws an exception when the provided class does not exist.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineDenormalizerEntityFinderClassException
     */
    public function testFindOneByThrowsExceptionWithNonExistentClass(): void
    {
        $registry = new ManagerRegistryStub(null);
        $instance = new DoctrineDenormalizerEntityFinder($registry);

        $this->expectException(DoctrineDenormalizerEntityFinderClassException::class);
        $this->expectExceptionMessage('The class "ThisIsNotARealClass" could not be found.');

        $instance->findOneBy('ThisIsNotARealClass', []);
    }
}
