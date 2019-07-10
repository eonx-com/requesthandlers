<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Request;

use LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException;
use LoyaltyCorp\RequestHandlers\Request\ParamConverterManager;
use ReflectionProperty;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Sensio\ParamConverterStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Request\ParamConverterManager
 */
class ParamConverterManagerTest extends TestCase
{
    /**
     * Tests adding providers and priority sorting.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testAddingConverters(): void
    {
        $manager = new ParamConverterManager();
        $manager->add($stub1 = new ParamConverterStub(), 0, 'stub1');
        $manager->add($stub2 = new ParamConverterStub(), 1, 'stub2');
        $manager->add($stub3 = new ParamConverterStub(), 2, 'stub3');
        $manager->add($stub4 = new ParamConverterStub(), 0, null);

        $expectedPriority = [$stub3, $stub2, $stub1, $stub4];
        $expectedNamed = [
            'stub1' => $stub1,
            'stub2' => $stub2,
            'stub3' => $stub3
        ];

        $priorityConverters = $manager->all();

        // Hax alert. Dont do this :(
        $reflectionProperty = new ReflectionProperty($manager, 'namedConverters');
        $reflectionProperty->setAccessible(true);
        $namedConverters = $reflectionProperty->getValue($manager);

        static::assertSame($expectedPriority, $priorityConverters);
        static::assertSame($expectedNamed, $namedConverters);
    }

    /**
     * Tests apply will bail when the property is already set to the expected class.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyAlreadySet(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub(), 0, null);

        $request = new Request();
        $request->attributes->set('property', new stdClass());

        $configuration = new ParamConverter([]);
        $configuration->setName('property');
        $configuration->setClass(stdClass::class);

        $manager->apply($request, [$configuration]);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests what happens when no param converters do the conversion.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyConverterApplied(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub([true], [true]), 0, null);

        $configuration = new ParamConverter([]);
        $configuration->setName('property');

        $manager->apply(new Request(), [$configuration]);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests what happens when no param converters do the conversion.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyConverterNoneApplied(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub(null, [false]), 0, null);

        $configuration = new ParamConverter([]);
        $configuration->setName('property');

        $this->expectException(ParamConverterMisconfiguredException::class);
        $this->expectExceptionMessage('The ParamConverter for "property" was not processed by any known converters.');

        $manager->apply(new Request(), [$configuration]);
    }

    /**
     * Tests apply failure when configured converter doesnt convert.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyNamedConverter(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub([true], [true]), null, 'existent');

        $request = new Request();

        $configuration = new ParamConverter([]);
        $configuration->setClass(stdClass::class);
        $configuration->setConverter('existent');
        $configuration->setName('property');

        $manager->apply($request, [$configuration]);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests apply will throw when named converter doesnt exist.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyNamedConverterBadName(): void
    {
        $manager = new ParamConverterManager();

        $request = new Request();

        $configuration = new ParamConverter([]);
        $configuration->setClass(stdClass::class);
        $configuration->setConverter('non-existent');
        $configuration->setName('property');

        $this->expectException(ParamConverterMisconfiguredException::class);
        $this->expectExceptionMessage(
            'No converter named "non-existent" found for conversion of parameter "property".'
        );

        $manager->apply($request, [$configuration]);
    }

    /**
     * Tests apply failure when configured converter doesnt convert.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyNamedConverterNotApplied(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub([false], [true]), null, 'existent');

        $request = new Request();

        $configuration = new ParamConverter([]);
        $configuration->setClass(stdClass::class);
        $configuration->setConverter('existent');
        $configuration->setName('property');

        $this->expectException(ParamConverterMisconfiguredException::class);
        $this->expectExceptionMessage(
            'The converter "existent" did not run for property "property".'
        );

        $manager->apply($request, [$configuration]);
    }

    /**
     * Tests apply will throw when named converter doesnt support the configuration.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ParamConverterMisconfiguredException
     */
    public function testApplyNamedConverterNotSupported(): void
    {
        $manager = new ParamConverterManager();
        $manager->add(new ParamConverterStub(null, [false]), null, 'existent');

        $request = new Request();

        $configuration = new ParamConverter([]);
        $configuration->setClass(stdClass::class);
        $configuration->setConverter('existent');
        $configuration->setName('property');

        $this->expectException(ParamConverterMisconfiguredException::class);
        $this->expectExceptionMessage(
            'Converter "existent" does not support conversion of parameter "property".'
        );

        $manager->apply($request, [$configuration]);
    }
}
