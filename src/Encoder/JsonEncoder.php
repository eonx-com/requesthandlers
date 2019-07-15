<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

/**
 * This json encoder extends base symfony encoder to add the ability to check
 * for empty request body and add in the default empty body.
 */
final class JsonEncoder extends BaseJsonEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if ($data === '') {
            $data = '{}';
        }

        return parent::decode($data, $format, $context);
    }
}
