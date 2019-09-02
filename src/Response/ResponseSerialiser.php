<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Response;

use DateTimeZone;
use EoneoPay\Utils\Interfaces\UtcDateTimeInterface;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseSerialiser implements ResponseSerialiserInterface
{
    /**
     * An array of attributes to ignore when normalising the response objects
     * into arrays.
     *
     * @var string[]
     */
    private $ignoredAttributes;

    /**
     * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    private $normalizer;

    /**
     * Constructor
     *
     * @param string[] $ignoredAttributes
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     */
    public function __construct(array $ignoredAttributes, NormalizerInterface $normalizer)
    {
        $this->ignoredAttributes = $ignoredAttributes;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function normalise(SerialisableResponseInterface $response): array
    {
        if ($this->normalizer->supportsNormalization($response) === false) {
            throw new MisconfiguredSerializerException(
                'The serialiser is not configured to support serialisable response objects.'
            );
        }

        try {
            // The json format used in this method call is a hint to any normalisers in the
            // serialiser that allows it to make assumptions about the data format we will
            // be returning. This does not preclude the use of the resulting normalised data
            // being serialised into XML by other middleware.
            $result = $this->normalizer->normalize($response, 'json', [
                'ignored_attributes' => $this->ignoredAttributes,
                // Force the normalisation of datetimes to UTC
                DateTimeNormalizer::FORMAT_KEY => UtcDateTimeInterface::FORMAT_ZULU,
                DateTimeNormalizer::TIMEZONE_KEY => new DateTimeZone('UTC')
            ]);
        } catch (ExceptionInterface $exception) {
            throw new ResponseNormaliserException(
                'An exception occurred while trying to serialise a response.',
                null,
                null,
                $exception
            );
        }

        if (\is_array($result) === false) {
            throw new MisconfiguredSerializerException(
                'The serialiser is not configured to return an array when normalising a response object.'
            );
        }

        return $result;
    }
}
