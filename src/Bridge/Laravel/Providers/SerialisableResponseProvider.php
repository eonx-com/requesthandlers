<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\ResponseSerialiserInterface;
use LoyaltyCorp\RequestHandlers\Response\ResponseSerialiser;

class SerialisableResponseProvider extends ServiceProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(SerialisableResponseMiddleware::class);

        $this->app->bind(
            ResponseSerialiserInterface::class,
            static function (Container $app): ResponseSerialiserInterface {
                $ignoredAttributes = ['_statusCode'];
                $normaliser = $app->make('requesthandlers_serializer');

                return new ResponseSerialiser($ignoredAttributes, $normaliser);
            }
        );
    }
}
