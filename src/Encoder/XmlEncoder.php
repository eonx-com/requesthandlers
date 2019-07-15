<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Encoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;

/**
 * This xml encoder extends base symfony encoder to add the ability to check
 * for empty request body and add in the default empty body.
 */
final class XmlEncoder extends BaseXmlEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        if ($data === '') {
            $data = '<xml></xml>';
        }

        return parent::decode($data, $format, $context);
    }
}
