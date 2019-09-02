<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

/**
 * @coversNothing
 */
class NormaliserStub implements NormalizerInterface
{
    /**
     * @var mixed
     */
    private $result;

    /**
     * @var bool
     */
    private $supported;

    /**
     * Constructor
     *
     * @param mixed $result
     * @param bool $supported
     */
    public function __construct($result, bool $supported)
    {
        $this->result = $result;
        $this->supported = $supported;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (($this->result instanceof Throwable) === true) {
            throw $this->result;
        }

        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->supported;
    }
}
