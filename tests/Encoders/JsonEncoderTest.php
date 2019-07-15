<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Encoders;

use LoyaltyCorp\RequestHandlers\Encoder\JsonEncoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Encoder\JsonEncoder
 */
class JsonEncoderTest extends TestCase
{
    /**
     * Test to make sure empty data gets processed by decode without fuss.
     *
     * @return void
     */
    public function testDecodeWorksWithEmptyData(): void
    {
        $encoder = new JsonEncoder();

        $expected = [];

        $result = $encoder->decode('', 'json');

        self::assertSame($expected, $result);
    }

    /**
     * Test to make sure null data gets processed by decode without fuss.
     *
     * @return void
     */
    public function testDecodeWorksWithNullData(): void
    {
        $encoder = new JsonEncoder();

        $expected = [];

        $result = $encoder->decode(null, 'json');

        self::assertSame($expected, $result);
    }
}
