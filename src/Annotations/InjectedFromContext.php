<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Annotations;

/**
 * This annotation is used on properties to indicate that they should only be injected from
 * the context of the request and not be populated with data from the request body.
 *
 * It allows for things like the object from the request URL to be resolved and injected
 * into the request object for additional validation purposes.
 *
 * @Annotation
 */
class InjectedFromContext
{
}
