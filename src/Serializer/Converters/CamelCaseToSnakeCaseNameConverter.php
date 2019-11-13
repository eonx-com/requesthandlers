<?php
declare(strict_types=1);

namespace LoyaltyCorp\RequestHandlers\Serializer\Converters;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * This class helps convert camel case property name to snake case. It will skip any part of the property name string
 * which it identifies as an array key.
 *
 * @PHPMD(PHPMD.IfStatementAssignment) PHPMD is complaining about it, but it doesnt happen.
 */
class CamelCaseToSnakeCaseNameConverter implements NameConverterInterface
{
    /**
     * List of attributes.
     *
     * @var mixed[]|null
     */
    private $attributes;

    /**
     * Is lower camel-case.
     *
     * @var bool
     */
    private $lowerCamelCase;

    /**
     * @param mixed[]|null $attributes The list of attributes to rename or null for all attributes
     * @param bool $lowerCamelCase Use lower camel-case style
     */
    public function __construct(?array $attributes = null, ?bool $lowerCamelCase = null)
    {
        $this->attributes = $attributes;
        $this->lowerCamelCase = $lowerCamelCase ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName): string
    {
        /** @var string $camelCasedName */
        $camelCasedName = \preg_replace_callback('/(^|_|\.)+(.)|\[.*?\]/', static function ($match) {
            // If the token starts with a '[' means property has array so do not perform any modifications,
            // just return same.
            if (\mb_strpos($match[0], '[') === 0) {
                return $match[0];
            }
            if (isset($match[1]) === true && $match[1] === '.') {
                return $match[0];
            }
            return \mb_strtoupper($match[2]);
        }, $propertyName) ?: '';

        if ($this->lowerCamelCase === true) {
            $camelCasedName = \lcfirst($camelCasedName);
        }

        if ($this->attributes === null || \in_array($camelCasedName, $this->attributes, true)) {
            return $camelCasedName;
        }

        return $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName): string
    {
        if ($this->attributes === null || \in_array($propertyName, $this->attributes, true) === true) {
            return \preg_replace_callback('/[A-Z]|\[.*?\]/', static function ($match) {
                // If the token starts with a '[' means property has array so do not perform any modifications,
                // just return same.
                if (\mb_strpos($match[0], '[') === 0) {
                    return $match[0];
                }
                /** @var string $normalized */
                $normalized = \preg_replace('/['.$match[0].']/', '_\\0', $match[0]);
                return \mb_strtolower($normalized);
            }, \lcfirst($propertyName)) ?: '';
        }

        return $propertyName;
    }
}
