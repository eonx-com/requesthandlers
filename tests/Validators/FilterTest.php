<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Validators;

use LoyaltyCorp\RequestHandlers\Validators\Filter;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Validators\Filter
 */
class FilterTest extends TestCase
{
    /**
     * Test that the annotation targets classes
     *
     * @return void
     */
    public function testTarget(): void
    {
        $classConstant = 'property';
        $constraint = new Filter();

        self::assertSame($classConstant, $constraint->getTargets());
    }
}
