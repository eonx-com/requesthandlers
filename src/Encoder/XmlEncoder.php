<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Encoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;

final class XmlEncoder extends BaseXmlEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if ($data === '' || $data === null) {
            $data = '<xml></xml>';
        }

        return parent::decode($data, $format, $context);
    }
}
