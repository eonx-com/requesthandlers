<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Serializer;

use DateTime;
use LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer
 */
class RequestBodySerializerTest extends TestCase
{
    /**
     * Tests that denormalize will catch and suppress any serialization exceptions
     * and return them on a getFailures() method.
     *
     * @return void
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalizeCatches(): void
    {
        $serializer = new RequestBodySerializer([
            new DateTimeNormalizer()
        ]);

        $result = $serializer->denormalize('PURPLE-ELEPHANT', DateTime::class, 'json', [
            'attribute' => 'elephant_colour'
        ]);

        self::assertSame('PURPLE-ELEPHANT', $result);
    }
}
