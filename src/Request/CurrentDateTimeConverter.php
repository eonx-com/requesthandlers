<?php

namespace LoyaltyCorp\RequestHandlers\Request;

use DateTime;
use DateTimeInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Create a date time on demand in controllers.
 */
class CurrentDateTimeConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        // Don't override existing values.
        if ($request->attributes->has($param) === true &&
            $value = $request->attributes->get($param) !== null) {
            return false;
        }

        $class = $configuration->getClass();
        $date = new $class('now');

        $request->attributes->set($param, $date);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if ($configuration->getClass() === null) {
            return false;
        }

        if (\is_subclass_of($configuration->getClass(), DateTimeInterface::class) ||
            \is_subclass_of($configuration->getClass(), DateTime::class)
        ) {
            return true;
        }
        return false;
    }
}
