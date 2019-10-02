<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Builder\Interfaces;

use LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface;

interface ObjectBuilderInterface
{
    /**
     * Builds a valid Request Object given the supplied json and optional additional
     * context.
     *
     * @param string $objectClass
     * @param string $json
     * @param mixed[]|null $context
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface
     */
    public function build(string $objectClass, string $json, ?array $context = null): RequestObjectInterface;

    /**
     * Used to build a request object with an array of context. Is used if there
     * is no point to providing JSON and properties are directly provided instead.
     *
     * @param string $objectClass
     * @param mixed[] $context
     *
     * @return \LoyaltyCorp\RequestHandlers\Request\RequestObjectInterface
     */
    public function buildWithContext(string $objectClass, array $context): RequestObjectInterface;
}
