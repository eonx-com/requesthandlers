<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Request;

use FOS\RestBundle\Serializer\SymfonySerializerAdapter;
use LoyaltyCorp\RequestHandlers\Encoder\JsonEncoder;
use LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Serializer\RequestBodySerializer;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\SerializerStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter
 */
class RequestBodyParamConverterTest extends TestCase
{
    /**
     * Tests that the param converter applies and configures the context
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testApply(): void
    {
        $object = new stdClass();
        $serializer = new SerializerStub($object);

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $request = $this->buildRequest('application/json', '{"content": "here"}');

        $request->attributes->set('attribute', 'value');

        $converter->apply($request, new ParamConverter([
            'name' => 'property',
            'class' => 'EntityClass'
        ]));

        static::assertSame($object, $request->attributes->get('property'));
    }

    /**
     * Tests that the param converter applies and configures the context
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testApplyThrows(): void
    {
        $this->expectException(RuntimeException::class);

        $serializer = new SerializerStub(new RuntimeException());
        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $request = new Request([], [], [], [], [], ['HTTP_CONTENT_TYPE' => 'application/json'], 'body');

        $this->expectException(RuntimeException::class);

        $converter->apply($request, new ParamConverter([
            'class' => 'EntityClass'
        ]));
    }

    /**
     * Tests that the param converter applies and configures the context
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testApplyWithNoContentType(): void
    {
        $this->expectException(InvalidContentTypeException::class);

        $serializer = new SerializerStub(new RuntimeException());

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $request = new Request();

        $converter->apply($request, new ParamConverter([
            'class' => 'EntityClass'
        ]));
    }

    /**
     * Tests that supports works
     *
     * @return void
     */
    public function testSupports(): void
    {
        $serializer = new SerializerStub();
        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $result = $converter->supports(new ParamConverter([
            'class' => RequestObjectStub::class
        ]));

        self::assertTrue($result);
    }

    /**
     * Test no json body when using default json encoder fails
     * with a Syntax Error. This is not the expected behaviour thus the
     * JsonEncoder will be extend to check for empty data string.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testWithNoJsonBodyFailsWithBaseEncoder(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Syntax error');

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter(
            new RequestBodySerializer(
                [new ArrayDenormalizer()],
                [new SymfonyJsonEncoder()]
            )
        ));
        $request = $this->buildRequest('application/json', '');

        $converter->apply($request, new ParamConverter([
            'name' => 'property',
            'class' => 'EntityClass'
        ]));
    }

    /**
     * Test param converter works fine when request content is empty
     * when using custom encoder.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testWithNoJsonBodyWithCustomEncoder(): void
    {
        $serializer = new RequestBodySerializer(
            [new ArrayDenormalizer()],
            [new JsonEncoder()]
        );

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter(
            $serializer
        ));
        $request = $this->buildRequest('application/json', '');

        $converter->apply($request, new ParamConverter([
            'name' => 'property',
            'class' => 'EntityClass'
        ]));

        $this->addToAssertionCount(1);
    }

    /**
     * Builds a request.
     *
     * @param null|string $contentType
     * @param null|string $content
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function buildRequest(?string $contentType = null, ?string $content = null): Request
    {
        $headers = [];
        if ($contentType !== null) {
            $headers['HTTP_CONTENT_TYPE'] = $contentType;
        }

        return new Request([], [], [], [], [], $headers, $content);
    }
}
