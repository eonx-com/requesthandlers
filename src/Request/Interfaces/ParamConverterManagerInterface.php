<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Request\Interfaces;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

interface ParamConverterManagerInterface
{
    /**
     * Adds a param converter to the manager.
     *
     * A null priority means the converter will not be iterated over while trying to process
     * a configuration and it must be explicitly named by the configuration.
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface $converter
     * @param int|null $priority
     * @param null|string $name
     *
     * @return void
     */
    public function add(ParamConverterInterface $converter, ?int $priority, ?string $name): void;

    /**
     * Applies param converter configurations to a request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter[] $configurations
     *
     * @return void
     */
    public function apply(Request $request, array $configurations): void;
}
