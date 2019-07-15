<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Encoders;

use LoyaltyCorp\RequestHandlers\Encoder\XmlEncoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Encoder\XmlEncoder
 */
class XmlEncoderTest extends TestCase
{
    /**
     * Test to make sure empty data gets processed by decode without fuss.
     *
     * @return void
     */
    public function testDecodeWorksWithEmptyData(): void
    {
        $encoder = new XmlEncoder();

        $expected = '';

        $result = $encoder->decode('', 'xml');

        self::assertSame($expected, $result);
    }

    /**
     * Test to make sure null data gets processed by decode without fuss.
     *
     * @return void
     */
    public function testDecodeWorksWithNullData(): void
    {
        $encoder = new XmlEncoder();

        $expected = '';

        $result = $encoder->decode(null, 'xml');

        self::assertSame($expected, $result);
    }
}
