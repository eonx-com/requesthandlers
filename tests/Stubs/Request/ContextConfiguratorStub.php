<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Request;

use FOS\RestBundle\Context\Context;
use LoyaltyCorp\RequestHandlers\Request\Interfaces\ContextConfiguratorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversNothing
 */
class ContextConfiguratorStub implements ContextConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(Context $context, Request $request): void
    {
        $context->setAttribute('configurated', true);
    }
}
