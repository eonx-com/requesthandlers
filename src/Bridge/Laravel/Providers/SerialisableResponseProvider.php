<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware;

class SerialisableResponseProvider extends ServiceProvider
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(
            SerialisableResponseMiddleware::class,
            static function (Container $app): SerialisableResponseMiddleware {
                $ignoredAttributes = ['_statusCode'];
                $normaliser = $app->make('requesthandlers_serializer');

                return new SerialisableResponseMiddleware($ignoredAttributes, $normaliser);
            }
        );
    }
}
