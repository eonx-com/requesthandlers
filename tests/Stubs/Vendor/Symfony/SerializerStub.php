<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony;

use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

class SerializerStub implements SerializerInterface
{
    /**
     * @var mixed[]
     */
    private $deserialiseCalls = [];

    /**
     * @var mixed|\Throwable
     */
    private $object;

    /**
     * Constructor
     *
     * @param mixed|\Throwable $object
     */
    public function __construct($object = null)
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function deserialize($data, $type, $format, ?array $context = null)
    {
        $this->deserialiseCalls[] = \compact('data', 'type', 'format', 'context');

        if ($this->object instanceof Throwable) {
            throw $this->object;
        }

        return $this->object;
    }

    /**
     * Returns calls to deserialise.
     *
     * @return mixed[]
     */
    public function getDeserialiseCalls(): array
    {
        return $this->deserialiseCalls;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, ?array $context = null): string
    {
        return '';
    }
}
