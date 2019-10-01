<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request\Interfaces;

use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\Request;

interface ContextConfiguratorInterface
{
    /**
     * Implementors are given the request body deserialisation context to configure
     * additional parameters in the context.
     *
     * @param \FOS\RestBundle\Context\Context $context
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function configure(Context $context, Request $request): void;
}
