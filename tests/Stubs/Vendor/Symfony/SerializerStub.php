<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony;

use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

class SerializerStub implements SerializerInterface
{
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
        if ($this->object instanceof Throwable) {
            throw $this->object;
        }

        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, ?array $context = null): string
    {
        return '';
    }
}
