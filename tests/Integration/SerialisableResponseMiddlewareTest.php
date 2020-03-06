<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Integration;

use Closure;
use Doctrine\Common\Persistence\ManagerRegistry as CommonManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse;
use EoneoPay\Utils\AnnotationReader;
use EoneoPay\Utils\Interfaces\AnnotationReaderInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers\ParamConverterProvider;
use LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers\SerialisableResponseProvider;
use LoyaltyCorp\RequestHandlers\Middleware\SerialisableResponseMiddleware;
use LoyaltyCorp\RequestHandlers\Response\Interfaces\SerialisableResponseInterface;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Responses\SerialisableResponse;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer\DoctrineDenormalizerEntityFinderStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ManagerRegistryStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * Tests that the middleware interacts with the serialiser correctly.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SerialisableResponseMiddlewareTest extends TestCase
{
    /**
     * Tests the process when we receive back something that isnt serialisable.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testNonSerialisableResponse(): void
    {
        $request = new Request();

        $lastStage = static function (Request $request): string {
            return 'hello';
        };

        $pipeline = $this->buildPipeline($request, $lastStage);

        $result = $pipeline->thenReturn();

        static::assertSame('hello', $result);
    }

    /**
     * Tests the process when we receive a SerialisableResponse
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testSerialisableResponse(): void
    {
        $request = new Request();

        $lastStage = static function (Request $request): SerialisableResponseInterface {
            return new SerialisableResponse(400);
        };

        $expected = new FormattedApiResponse(
            [
                'purple' => 'elephants',
                'local_time' => '2019-01-01T16:04:05Z',
                'utc_time' => '2019-02-03T04:05:06Z'
            ],
            400
        );

        $pipeline = $this->buildPipeline($request, $lastStage);

        $result = $pipeline->thenReturn();

        static::assertEquals($expected, $result);
    }

    /**
     * Builds the full chain of dependencies.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $lastStage
     *
     * @return \Illuminate\Pipeline\Pipeline
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function buildPipeline(Request $request, Closure $lastStage): Pipeline
    {
        $app = new ApplicationStub();
        $app->instance(Container::class, $app);
        $app->bind(DoctrineDenormalizerEntityFinderInterface::class, DoctrineDenormalizerEntityFinderStub::class);
        $registry = new ManagerRegistryStub();
        $app->instance(ManagerRegistry::class, $registry);
        $app->instance(CommonManagerRegistry::class, $registry);
        $app->bind(AnnotationReaderInterface::class, AnnotationReader::class);
        (new ParamConverterProvider($app))->register();
        (new SerialisableResponseProvider($app))->register();

        $pipeline = new Pipeline(null);
        $pipeline->send($request);
        $pipeline->through([$app->make(SerialisableResponseMiddleware::class), $lastStage]);

        return $pipeline;
    }
}
