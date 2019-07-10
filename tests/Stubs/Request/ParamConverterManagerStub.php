<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Request;

use LoyaltyCorp\RequestHandlers\Request\Interfaces\ParamConverterManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ParamConverterManagerStub implements ParamConverterManagerInterface
{
    /**
     * @var mixed[]
     */
    private $configurations;

    /**
     * {@inheritdoc}
     */
    public function add(ParamConverterInterface $converter, ?int $priority, ?string $name): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, array $configurations): void
    {
        $this->configurations = $configurations;
    }

    /**
     * Returns configurations from last call.
     *
     * @return mixed[]
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }
}
