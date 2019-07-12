<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

final class JsonEncoder extends BaseJsonEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if ($data === '' || $data === null) {
            $data = '{}';
        }

        return parent::decode($data, $format, $context);
    }
}
