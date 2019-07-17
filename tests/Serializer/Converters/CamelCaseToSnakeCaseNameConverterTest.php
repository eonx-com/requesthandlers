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
     * Get property names data set for name converter tests.
     *
     * @return iterable
     */
    public function getPropertyNamesDataSet(): iterable
    {
        yield [
            'Property name with array key 1' => [
                'denormalized' => 'headerStuff[APPLICATION_ID].property',
                'normalized' => 'header_stuff[APPLICATION_ID].property'
            ]
        ];
        yield [
            'Property name with array key 2' => [
                'denormalized' => 'headerStuffStuff[APPLICATION_ID].property',
                'normalized' => 'header_stuff_stuff[APPLICATION_ID].property'
            ]
        ];
        yield [
            'Property name with array key 3' => [
                'denormalized' => 'headerStuff[APPLICATION_ID].propertyId',
                'normalized' => 'header_stuff[APPLICATION_ID].property_id'
            ]
        ];
        yield [
            'Property name no array key 1' => [
                'denormalized' => 'propertyName',
                'normalized' => 'property_name'
            ]
        ];
        yield [
            'Property name no array key 2' => [
                'denormalized' => 'property',
                'normalized' => 'property'
            ]
        ];
        yield [
            'Property name no array key 3' => [
                'denormalized' => 'property\((Name',
                'normalized' => 'property\((_name'
            ]
        ];
        yield [
            'Property name no array key 4' => [
                'denormalized' => 'property[NAME',
                'normalized' => 'property[_n_a_m_e'
            ]
        ];
    }

    /**
     * Test de-normalizing a property name results in expected value.
     *
     * @param mixed[] $data
     *
     * @return void
     *
     * @dataProvider getPropertyNamesDataSet()
     */
    public function testDenormalize(array $data): void
    {
        $actualDenormalized = $this->getConverter()->denormalize($data['normalized']);

        static::assertSame($data['denormalized'], $actualDenormalized);
    }

    /**
     * Test de-normalizing a property name results in expected value when attributes are supplied to converter.
     *
     * @return void
     */
    public function testDenormalizeWithProvidedAttributes(): void
    {
        $actualDenormalized = $this->getConverter(['random'])->denormalize('property');

        static::assertSame('property', $actualDenormalized);
    }

    /**
     * Test normalizing a property name results in expected value.
     *
     * @param mixed[] $data
     *
     * @return void
     *
     * @dataProvider getPropertyNamesDataSet()
     */
    public function testNormalize(array $data): void
    {
        $actualNormalized = $this->getConverter()->normalize($data['denormalized']);

        static::assertSame($data['normalized'], $actualNormalized);
    }

    /**
     * Test normalizing a property name results in expected value when attributes are supplied to converter.
     *
     * @return void
     */
    public function testNormalizeWithProvidedAttributes(): void
    {
        $actualNormalized = $this->getConverter(['random'])->normalize('property');

        static::assertSame('property', $actualNormalized);
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
