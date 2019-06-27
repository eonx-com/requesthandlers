<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\Symfony\Translator;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.ShortVariable) From interface docs
 */
class TranslatorStub implements TranslatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function trans(
        $id,
        ?array $parameters = null,
        $domain = null,
        $locale = null
    ): string {
        return $id;
    }
}
