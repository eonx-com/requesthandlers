<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer\Converters;

use LoyaltyCorp\RequestHandlers\Serializer\Converters\CamelCaseToSnakeCaseNameConverter;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\Converters\CamelCaseToSnakeCaseNameConverter
 */
class CamelCaseToSnakeCaseNameConverterTest extends TestCase
{
    /**
     * Test de-normalizing a property name results in expected value.
     *
     * @return void
     */
    public function testDenormalize(): void
    {
        $expectedValue1 = 'headerStuff[APPLICATION_ID].property';
        $expectedValue2 = 'headerStuffStuff[APPLICATION_ID].property';
        $expectedValue3 = 'headerStuff[APPLICATION_ID].propertyId';
        $expectedValue4 = 'propertyName';
        $expectedValue5 = 'property';

        $actualValue1 = $this->getConverter()->denormalize('header_stuff[APPLICATION_ID].property');
        $actualValue2 = $this->getConverter()->denormalize('header_stuff_stuff[APPLICATION_ID].property');
        $actualValue3 = $this->getConverter()->denormalize('header_stuff[APPLICATION_ID].property_id');
        $actualValue4 = $this->getConverter()->denormalize('property_name');
        $actualValue5 = $this->getConverter(['random'])->denormalize('property');

        static::assertSame($expectedValue1, $actualValue1);
        static::assertSame($expectedValue2, $actualValue2);
        static::assertSame($expectedValue3, $actualValue3);
        static::assertSame($expectedValue4, $actualValue4);
        static::assertSame($expectedValue5, $actualValue5);
    }

    /**
     * Test normalizing a property name results in expected value.
     *
     * @return void
     */
    public function testNormalize(): void
    {
        $expectedValue1 = 'header_stuff[APPLICATION_ID].property';
        $expectedValue2 = 'header_stuff_stuff[APPLICATION_ID].property';
        $expectedValue3 = 'header_stuff[APPLICATION_ID].property_id';
        $expectedValue4 = 'property_name';
        $expectedValue5 = 'property';

        $actualValue1 = $this->getConverter()->normalize('headerStuff[APPLICATION_ID].property');
        $actualValue2 = $this->getConverter()->normalize('headerStuffStuff[APPLICATION_ID].property');
        $actualValue3 = $this->getConverter()->normalize('headerStuff[APPLICATION_ID].propertyId');
        $actualValue4 = $this->getConverter()->normalize('propertyName');
        $actualValue5 = $this->getConverter(['random'])->normalize('property');

        static::assertSame($expectedValue1, $actualValue1);
        static::assertSame($expectedValue2, $actualValue2);
        static::assertSame($expectedValue3, $actualValue3);
        static::assertSame($expectedValue4, $actualValue4);
        static::assertSame($expectedValue5, $actualValue5);
    }

    /**
     * Get camel case to snake case converter.
     *
     * @param mixed[]|null $attributes List of attributes
     * @param bool|null $isLowerCamelCase Is lower camel-case.
     *
     * @return \LoyaltyCorp\RequestHandlers\Serializer\Converters\CamelCaseToSnakeCaseNameConverter
     */
    private function getConverter(
        ?array $attributes = null,
        ?bool $isLowerCamelCase = null
    ): CamelCaseToSnakeCaseNameConverter {
        return new CamelCaseToSnakeCaseNameConverter($attributes, $isLowerCamelCase);
    }
}
