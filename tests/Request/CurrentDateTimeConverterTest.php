<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Request;

use DateTime as BaseDateTime;
use EoneoPay\Utils\DateTime;
use LoyaltyCorp\RequestHandlers\Request\CurrentDateTimeConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Request\CurrentDateTimeConverter
 */
class CurrentDateTimeConverterTest extends TestCase
{
    /**
     * Test the application of the DateTime field.
     *
     * @return void
     */
    public function testApply(): void
    {
        $request = new Request([]);
        $param = new ParamConverter(['class' => DateTime::class, 'name' => 'now']);
        $converter = $this->getConverter();

        $response = $converter->apply($request, $param);

        self::assertTrue($response);
        self::assertInstanceOf(DateTime::class, $request->attributes->get('now'));
    }

    /**
     * Test skipping the application of the datetime field that is already filled.
     *
     * @return void
     */
    public function testFailingApply(): void
    {
        $request = new Request([], [], ['now' => 'already set']);
        $param = new ParamConverter(['class' => DateTime::class, 'name' => 'now']);
        $converter = $this->getConverter();

        $response = $converter->apply($request, $param);

        self::assertFalse($response);
        self::assertNull($request->attributes->get('already set'));
    }

    /**
     * Test that returning a BaseDateTime is supported.
     *
     * @return void
     */
    public function testSupportsBaseDateTime(): void
    {
        $param = new ParamConverter(['class' => BaseDateTime::class]);
        $converter = $this->getConverter();

        $supports = $converter->supports($param);

        self::assertTrue($supports);
    }

    /**
     * Test that returning a EoneoPayUtils datetime is supported.
     *
     * @return void
     */
    public function testSupportsEoneoPayDateTime(): void
    {
        $param = new ParamConverter(['class' => DateTime::class]);
        $converter = $this->getConverter();

        $supports = $converter->supports($param);

        self::assertTrue($supports);
    }

    /**
     * Test that returning a non-datetime classes are not supported.
     *
     * @return void
     */
    public function testNotSupportsNonDateTime(): void
    {
        $param = new ParamConverter(['class' => stdClass::class]);
        $converter = $this->getConverter();

        $supports = $converter->supports($param);

        self::assertFalse($supports);
    }

    /**
     * Test that a missing class definition is not supported.
     *
     * @return void
     */
    public function testFalseForMissingClass(): void
    {
        $param = new ParamConverter([]);
        $converter = $this->getConverter();

        $supports = $converter->supports($param);

        self::assertFalse($supports);
    }

    /**
     * Get a CurrentDateTimeConverter for testing.
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\CurrentDateTimeConverter
     */
    private function getConverter(): CurrentDateTimeConverter
    {
        return new CurrentDateTimeConverter();
    }
}
