<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class Controller
{
    /**
     * A basic controller method with no annotations.
     *
     * @return void
     */
    public function basicMethod(): void
    {
    }

    /**
     * A controller method with nothing for the RequestIntegration to do.
     *
     * @param string $baz
     * @param \Tests\LoyaltyCorp\RequestHandlers\Integration\Fixtures\ThingRequest $request
     *
     * @return void
     *
     * @ParamConverter("request")
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function doThing(string $baz, ThingRequest $request): void
    {
    }
}
