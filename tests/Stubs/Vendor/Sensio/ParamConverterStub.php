<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Sensio;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ParamConverterStub implements ParamConverterInterface
{
    /**
     * Returns for the apply function.
     *
     * @var bool[]
     */
    private $returnApply;

    /**
     * Returns for the supports function.
     *
     * @var bool[]
     */
    private $returnSupports;

    /**
     * Constructor
     *
     * @param bool[]|null $returnApply
     * @param bool[]|null $returnSupports
     */
    public function __construct(?array $returnApply = null, ?array $returnSupports = null)
    {
        $this->returnApply = $returnApply ?? [false];
        $this->returnSupports = $returnSupports ?? [false];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        return \array_shift($this->returnApply) ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return \array_shift($this->returnSupports) ?? true;
    }
}
