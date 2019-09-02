<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Middleware;

use Closure;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use Illuminate\Http\Request;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;

class SerialisableResponseMiddleware
{
    /**
     * @var \LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface
     */
    private $responseSerialiser;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface $responseSerialiser
     */
    public function __construct(ResponseSerialiserInterface $responseSerialiser)
    {
        $this->responseSerialiser = $responseSerialiser;
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

        $normalised = $this->responseSerialiser->normalise($response);

        return new FormattedApiResponse($normalised, $response->getStatusCode());
    }
}
