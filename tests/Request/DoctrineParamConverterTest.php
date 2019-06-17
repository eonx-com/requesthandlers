<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Request;

use Exception;
use InvalidArgumentException;
use LogicException;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException;
use LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException;
use LoyaltyCorp\RequestHandlers\Request\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as RealDoctrineParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

class DoctrineParamConverterTest extends TestCase
{
    /**
     * Tests InvalidArgumentException
     *
     * @return void
     */
    public function testExceptionHandlingInvalidArgument(): void
    {
        $throwing = new InvalidArgumentException('whoops');

        $converter = $this->getConverter($throwing);

        $thrown = null;
        try {
            $converter->apply(new Request(), new ParamConverter([]));
        } catch (Exception $thrown) {
        }

        static::assertInstanceOf(DoctrineParamConverterMisconfiguredException::class, $thrown);
        static::assertSame($throwing, $thrown->getPrevious());
        static::assertSame('DoctrineParamConverter is misconfigured. whoops', $thrown->getMessage());
    }

    /**
     * Tests Logic
     *
     * @return void
     */
    public function testExceptionHandlingLogic(): void
    {
        $throwing = new LogicException('whoops');

        $converter = $this->getConverter($throwing);

        $thrown = null;
        try {
            $converter->apply(new Request(), new ParamConverter([]));
        } catch (Exception $thrown) {
        }

        static::assertInstanceOf(DoctrineParamConverterMisconfiguredException::class, $thrown);
        static::assertSame($throwing, $thrown->getPrevious());
        static::assertSame('DoctrineParamConverter is misconfigured. whoops', $thrown->getMessage());
    }

    /**
     * Tests NotFound
     *
     * @return void
     */
    public function testExceptionHandlingNotFound(): void
    {
        $throwing = new NotFoundHttpException('whoops');

        $converter = $this->getConverter($throwing);

        $thrown = null;
        try {
            $converter->apply(new Request(), new ParamConverter([
                'class' => 'Entities\\Coupon',
                'name' => 'coupon'
            ]));
        } catch (Exception $thrown) {
        }

        static::assertInstanceOf(DoctrineParamConverterEntityNotFoundException::class, $thrown);
        /**
         * @var \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException $thrown
         */
        static::assertSame($throwing, $thrown->getPrevious());
        static::assertSame('coupon could not be found', $thrown->getMessage());
        static::assertSame('Entities\\Coupon', $thrown->getEntityClass());
    }

    /**
     * Tests Return
     *
     * @return void
     *
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterEntityNotFoundException
     * @throws \LoyaltyCorp\RequestHandlers\Exceptions\DoctrineParamConverterMisconfiguredException
     */
    public function testSuccess(): void
    {
        $converter = $this->getConverter();

        $converter->apply(new Request(), new ParamConverter([]));

        $this->addToAssertionCount(1);
    }

    /**
     * Tests Supports
     *
     * @return void
     */
    public function testSupport(): void
    {
        $converter = $this->getConverter();

        $converter->supports(new ParamConverter([]));

        $this->addToAssertionCount(1);
    }

    /**
     * Configures the DoctrineParamConverter wrapper
     *
     * @param \Exception $toThrow
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\DoctrineParamConverter
     */
    private function getConverter(?Exception $toThrow = null): DoctrineParamConverter
    {
        /**
         * Mocking because there is no DoctrineParamConverterInterface to use for a stub.
         */
        $mock = $this->createMock(RealDoctrineParamConverter::class);

        $mock->method('apply')->willReturn(true);

        if (($toThrow instanceof Exception) === true) {
            $mock->method('apply')->willThrowException($toThrow);
        }

        $mock->method('supports')->willReturn(true);

        /**
         * @var \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter $mock
         */

        return new DoctrineParamConverter($mock);
    }
}
