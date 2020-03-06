<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Integration;

use Doctrine\Common\Persistence\ManagerRegistry;
use EoneoPay\Utils\AnnotationReader;
use EoneoPay\Utils\DateTime;
use EoneoPay\Utils\Interfaces\AnnotationReaderInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use LoyaltyCorp\RequestHandlers\Bridge\Laravel\Providers\ParamConverterProvider;
use LoyaltyCorp\RequestHandlers\Middleware\ParamConverterMiddleware;
use LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware;
use LoyaltyCorp\RequestHandlers\Serializer\Interfaces\DoctrineDenormalizerEntityFinderInterface;
use Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\Controller;
use Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Serializer\DoctrineDenormalizerEntityFinderStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Doctrine\Common\Persistence\ManagerRegistryStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * Tests all services defined and required by this package as a single unit
 * to ensure that the expected behaviour is correct.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestIntegrationTest extends TestCase
{
    /**
     * Returns data for testSuccess
     *
     * @return string[]
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getSuccessData(): iterable
    {
        $json = <<<JSON
{
  "amount": "10.00",
  "date": "2019-01-02T03:04:05Z",
  "float": 0.75,
  "int": -10,
  "string": "string"
}
JSON;

        yield 'json' => [
            'content' => $json,
            'contentType' => 'application/json',
            'expectedResult' => [
                'amount' => '10.00',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $json = <<<JSON
{
  "amount": "10.00",
  "date": "2019-01-02T03:04:05Z",
  "float": "0.75",
  "int": -10,
  "string": "string"
}
JSON;

        yield 'json with strings' => [
            'content' => $json,
            'contentType' => 'application/json',
            'expectedResult' => [
                'amount' => '10.00',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $json = <<<JSON
{
  "amount": 10,
  "date": "2019-01-02T03:04:05Z",
  "float": 0.75,
  "int": -10,
  "string": "string"
}
JSON;

        yield 'json with ints' => [
            'content' => $json,
            'contentType' => 'application/json',
            'expectedResult' => [
                'amount' => '10',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $json = <<<JSON
{
  "amount": 10.00,
  "date": "2019-01-02T03:04:05Z",
  "float": 0.75,
  "int": -10,
  "string": "string"
}
JSON;

        yield 'json with floats' => [
            'content' => $json,
            'contentType' => 'application/json',
            'expectedResult' => [
                'amount' => '10',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $xml = <<<XML
<?xml version="1.0"?>
<document>
  <amount>10.00</amount>
  <date>2019-01-02T03:04:05Z</date>
  <float>0.75</float>
  <int>-10</int>
  <string>string</string>
</document>
XML;

        yield 'xml' => [
            'content' => $xml,
            'contentType' => 'text/xml',
            'expectedResult' => [
                'amount' => '10.00',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $xml = <<<XML
<?xml version="1.0"?>
<document>
  <amount>10</amount>
  <date>2019-01-02T03:04:05Z</date>
  <float>0.75</float>
  <int>-10</int>
  <string>string</string>
</document>
XML;

        yield 'xml with int amount for string' => [
            'content' => $xml,
            'contentType' => 'text/xml',
            'expectedResult' => [
                'amount' => '10',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];

        $xml = <<<XML
<?xml version="1.0"?>
<document>
  <amount><![CDATA[10.00]]></amount>
  <date><![CDATA[2019-01-02T03:04:05Z]]></date>
  <float><![CDATA[0.75]]></float>
  <int><![CDATA[-10]]></int>
  <string><![CDATA[string]]></string>
</document>
XML;

        yield 'xml with cdata' => [
            'content' => $xml,
            'contentType' => 'text/xml',
            'expectedResult' => [
                'amount' => '10.00',
                'date' => new DateTime('2019-01-02T03:04:05Z'),
                'float' => 0.75,
                'int' => -10,
                'string' => 'string'
            ]
        ];
    }

    /**
     * Tests the process when the request is empty.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testEmptyRequest(): void
    {
        $request = $this->buildRequest(Controller::class, 'basicMethod');

        $pipeline = $this->buildPipeline($request);

        $pipeline->thenReturn();

        // Asserting that no exceptions occurred.
        $this->addToAssertionCount(1);
    }

    /**
     * Test successful request with json.
     *
     * @param string $content
     * @param string $contentType
     * @param mixed[] $expectedResult
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @dataProvider getSuccessData
     */
    public function testSuccess(string $content, string $contentType, array $expectedResult): void
    {
        $request = $this->buildRequest(
            Controller::class,
            'doThing',
            $content,
            $contentType,
            ['baz' => 'flub']
        );

        $pipeline = $this->buildPipeline($request);

        $pipeline->thenReturn();

        // Assert that the middleware added additional route attributes into the attribute bag
        static::assertSame('flub', $request->attributes->get('baz'));

        $thing = $request->attributes->get('request');

        // Assert that ThingRequest got built by the param converter
        static::assertInstanceOf(ThingRequest::class, $thing);

        /**
         * @var \Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest $thing
         */

        static::assertSame('flub', $thing->getBaz());
        static::assertEquals($expectedResult, $thing->toArray());

        // Assert that the Middleware put $thing into the laravel route
        $actualThing = $request->route() === null ? null : $request->route()[2]['request'];
        static::assertSame($thing, $actualThing);
    }

    /**
     * Test successful request with json.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testInjectFromContext(): void
    {
        $json = <<<JSON
{
  "amount": "10.00",
  "baz": "purple",
  "date": "2019-01-02T03:04:05Z",
  "float": 0.75,
  "int": -10,
  "string": "string"
}
JSON;

        $request = $this->buildRequest(
            Controller::class,
            'doThing',
            $json,
            'application/json',
            []
        );

        $pipeline = $this->buildPipeline($request);

        $pipeline->thenReturn();

        $thing = $request->attributes->get('request');

        // Assert that ThingRequest got built by the param converter
        static::assertInstanceOf(ThingRequest::class, $thing);

        /**
         * @var \Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest $thing
         */

        static::assertNull($thing->getBaz());
    }

    /**
     * Test validation failure.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testValidationFailure(): void
    {
        $json = <<<JSON
{}
JSON;

        // Expected violations from ThingRequest as a debug string output.
        $expectedViolations = <<<VIOLATIONS
Object(Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest).amount:
    This value should not be null. (code ad32d13f-c3d4-423b-909a-857b961eb720)
Object(Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest).date:
    This value should not be null. (code ad32d13f-c3d4-423b-909a-857b961eb720)
Object(Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest).float:
    This value should not be null. (code ad32d13f-c3d4-423b-909a-857b961eb720)
Object(Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest).int:
    This value should not be null. (code ad32d13f-c3d4-423b-909a-857b961eb720)
Object(Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest).string:
    This value should not be null. (code ad32d13f-c3d4-423b-909a-857b961eb720)

VIOLATIONS;

        $request = $this->buildRequest(Controller::class, 'doThing', $json);

        $pipeline = $this->buildPipeline($request);

        try {
            $pipeline->thenReturn();
        } catch (RequestValidationExceptionStub $exception) {
            /** @var \Symfony\Component\Validator\ConstraintViolationList $violations */
            $violations = $exception->getViolations();

            static::assertSame($expectedViolations, (string)$violations);

            return;
        }

        static::fail('Exception not thrown');
    }

    /**
     * Builds the full chain of dependencies.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Pipeline\Pipeline
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function buildPipeline(Request $request): Pipeline
    {
        $app = new ApplicationStub();
        $app->instance(Container::class, $app);
        $app->bind(DoctrineDenormalizerEntityFinderInterface::class, DoctrineDenormalizerEntityFinderStub::class);
        $registry = new ManagerRegistryStub();
        $app->instance(ManagerRegistry::class, $registry);
        (new ParamConverterProvider($app))->register();
        $app->bind(AnnotationReaderInterface::class, AnnotationReader::class);

        $pcm = $app->make(ParamConverterMiddleware::class);
        $validatingMiddleware = $app->make(ValidatingMiddleware::class);

        $pipeline = new Pipeline(null);
        $pipeline->send($request);
        $pipeline->through([$pcm, $validatingMiddleware]);

        return $pipeline;
    }

    /**
     * Builds a request object.
     *
     * @param string $controller
     * @param string $method
     * @param null|string $content
     * @param null|string $contentType
     * @param mixed[]|null $routeAttributes
     *
     * @return \Illuminate\Http\Request
     */
    private function buildRequest(
        string $controller,
        string $method,
        ?string $content = null,
        ?string $contentType = null,
        ?array $routeAttributes = null
    ): Request {
        $request = new Request();
        $request->initialize([], [], [], [], [], [
            'HTTP_CONTENT_TYPE' => $contentType ?? 'application/json'
        ], $content);
        $request->setRouteResolver(
            static function () use ($controller, $method, $routeAttributes) {
                return [
                    1 => ['uses' => \sprintf('%s@%s', $controller, $method)],
                    2 => $routeAttributes ?? []
                ];
            }
        );

        return $request;
    }
}
