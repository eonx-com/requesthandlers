<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Responses;

use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException;
use LoyaltyCorp\RequestHandlers\Response\ResponseSerialiser;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\SerialisableResponse;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\NormaliserStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class ResponseSerialiserTest extends TestCase
{
    /**
     * Tests that an exception is thrown when the underlying serialiser
     * is configured to not support a response object.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testNormaliseReturnsNonArray(): void
    {
        $normaliser = new NormaliserStub('green', true);
        $serialiser = new ResponseSerialiser([], $normaliser);

        $this->expectException(MisconfiguredSerializerException::class);
        $this->expectExceptionMessage(
            'The serialiser is not configured to return an array when normalising a response object.'
        );

        $serialiser->normalise(new SerialisableResponse(204));
    }

    /**
     * Tests that an exception is thrown when the underlying serialiser
     * is configured to not support a response object.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testNormalise(): void
    {
        $normaliser = new NormaliserStub(['green' => 'apples'], true);
        $serialiser = new ResponseSerialiser([], $normaliser);

        $expected = ['green' => 'apples'];

        $result = $serialiser->normalise(new SerialisableResponse(204));

        static::assertSame($expected, $result);
    }

    /**
     * Tests that an exception is thrown when the underlying serialiser
     * is configured to not support a response object.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     */
    public function testNormaliseThrows(): void
    {
        $inner = new CircularReferenceException();
        $normaliser = new NormaliserStub($inner, true);
        $serialiser = new ResponseSerialiser([], $normaliser);

        try {
            $serialiser->normalise(new SerialisableResponse(204));
        } catch (ResponseNormaliserException $exception) {
            static::assertSame($inner, $exception->getPrevious());
            static::assertSame('An exception occurred while trying to serialise a response.', $exception->getMessage());

            return;
        }

        self::fail('Exception was not caught.');
    }

    /**
     * Tests that an exception is thrown when the underlying serialiser
     * is configured to not support a response object.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function testNormaliseUnsupported(): void
    {
        $normaliser = new NormaliserStub(null, false);
        $serialiser = new ResponseSerialiser([], $normaliser);

        $this->expectException(MisconfiguredSerializerException::class);
        $this->expectExceptionMessage('The serialiser is not configured to support serialisable response objects.');

        $serialiser->normalise(new SerialisableResponse(204));
    }
}
