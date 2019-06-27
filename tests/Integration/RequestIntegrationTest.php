<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Integration;

use DateTime as BaseDateTime;
use DateTimeZone;
use EoneoPay\Utils\AnnotationReader;
use EoneoPay\Utils\Bridge\Lumen\Resolvers\ControllerResolver;
use EoneoPay\Utils\DateTime;
use FOS\RestBundle\Serializer\SymfonySerializerAdapter;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use LoyaltyCorp\RequestHandlers\Middleware\ParamConverterMiddleware;
use LoyaltyCorp\RequestHandlers\Middleware\ValidatingMiddleware;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\Controller;
use Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Exceptions\RequestValidationExceptionStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * Tests all services defined and required by this package as a single unit
 * to ensure that the expected behaviour is correct.
 */
class RequestIntegrationTest extends TestCase
{
    /**
     * Returns data for testSuccess
     *
     * @return string[]
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
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
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
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
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
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
        static::assertSame($thing, $request->route()[2]['request']);
    }

    /**
     * Test validation failure.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
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
     * Builds the ParamConverterManager
     *
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager
     */
    private function buildParamConverters(SerializerInterface $serializer): ParamConverterManager
    {
        $requestBody = new RequestBodyParamConverter(
            new SymfonySerializerAdapter($serializer)
        );

        $manager = new ParamConverterManager();
        $manager->add($requestBody);

        return $manager;
    }

    /**
     * Builds the full chain of dependencies.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Pipeline\Pipeline
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    private function buildPipeline(Request $request): Pipeline
    {
        $annotationReader = new AnnotationReader();
        $container = new ApplicationStub();

        $serializer = $this->buildSerializer($annotationReader);
        $validator = $this->buildValidator($annotationReader);
        $paramConverters = $this->buildParamConverters($serializer);

        $controllerListener = new ControllerListener($annotationReader);
        $controllerResolver = new ControllerResolver($container);
        $converterListener = new ParamConverterListener($paramConverters);

        $pcm = new ParamConverterMiddleware(
            $controllerListener,
            $controllerResolver,
            $converterListener
        );

        $validatingMiddleware = new ValidatingMiddleware($validator);

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

    /**
     * Builds the serializer under test.
     *
     * @param \EoneoPay\Utils\AnnotationReader $reader
     *
     * @return \LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer
     */
    private function buildSerializer(AnnotationReader $reader): RequestBodySerializer
    {
        $metadataFactory = new ClassMetadataFactory(new AnnotationLoader($reader));

        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();

        $normalizers = [
            new ArrayDenormalizer(),
            new DateIntervalNormalizer(
                [
                    DateIntervalNormalizer::FORMAT_KEY => 'P%mM'
                ]
            ),
            new DateTimeNormalizer(
                [
                    DateTimeNormalizer::FORMAT_KEY => BaseDateTime::RFC3339
                ],
                new DateTimeZone('UTC')
            ),
            new PropertyNormalizer(
                $metadataFactory,
                new CamelCaseToSnakeCaseNameConverter(),
                new PropertyInfoExtractor(
                    [$reflectionExtractor],
                    [$phpDocExtractor],
                    [$phpDocExtractor],
                    [$reflectionExtractor],
                    [$reflectionExtractor]
                )
            )
        ];

        $encoders = [
            new JsonEncoder(),
            new XmlEncoder()
        ];

        return new RequestBodySerializer($normalizers, $encoders);
    }

    /**
     * Builds the validator under test.
     *
     * @param \EoneoPay\Utils\AnnotationReader $reader
     *
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private function buildValidator(AnnotationReader $reader): ValidatorInterface
    {
        $constraintFactory = new ConstraintValidatorFactory();

        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping($reader)
            ->setConstraintValidatorFactory($constraintFactory)
            ->getValidator();
    }
}
