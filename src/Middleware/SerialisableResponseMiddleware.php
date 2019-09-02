<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use DateTimeZone;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use EoneoPay\Utils\Interfaces\UtcDateTimeInterface;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException;
use LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SerialisableResponseMiddleware
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
     * Checks if the response object is an instanceof SerialisableResponse and
     * convert it into an array format to be handled by a layer further down
     * the chain.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed|\EoneoPay\ApiFormats\Interfaces\FormattedApiResponseInterface
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (($response instanceof SerialisableResponseInterface) === false) {
            return $response;
        }

        /**
         * @var \LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface $response
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === chec
         */

        $normalised = $this->normalise($response);

        return new FormattedApiResponse($normalised, $response->getStatusCode());
    }

    /**
     * Performs a normalisation on the response if one can be performed.
     *
     * @param \LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface $response
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\MisconfiguredSerializerException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\ResponseNormaliserException
     */
    private function normalise(SerialisableResponseInterface $response)
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

        return $result;
    }
}
