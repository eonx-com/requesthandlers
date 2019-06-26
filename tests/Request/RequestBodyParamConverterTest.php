<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Request;

use FOS\RestBundle\Serializer\SymfonySerializerAdapter;
use LoyaltyCorp\RequestHandlers\Exceptions\InvalidContentTypeException;
use LoyaltyCorp\RequestHandlers\Request\RequestBodyParamConverter;
use LoyaltyCorp\RequestHandlers\Serializer\PropertyNormalizer;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Request\RequestObjectStub;
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
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::once())
            ->method('deserialize')
            ->with('body', 'EntityClass', 'json', [
                PropertyNormalizer::EXTRA_PARAMETERS => [
                    'attribute' => 'value'
                ],
                'version' => null,
                'maxDepth' => null,
                'enable_max_depth' => null
            ]);

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $request = new Request([], [], [], [], [], ['HTTP_CONTENT_TYPE' => 'application/json'], 'body');
        $request->attributes->set('attribute', 'value');

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
    public function testApplyThrows(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::once())
            ->method('deserialize')
            ->willThrowException(new RuntimeException());

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

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::once())
            ->method('deserialize')
            ->willThrowException(new RuntimeException());

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
        $serializer = $this->createMock(SerializerInterface::class);

        $converter = new RequestBodyParamConverter(new SymfonySerializerAdapter($serializer));

        $result = $converter->supports(new ParamConverter([
            'class' => RequestObjectStub::class
        ]));

        self::assertTrue($result);
    }
}
