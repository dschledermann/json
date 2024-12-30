<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * Lowercase the key
 */
#[Attribute]
final class ToLower implements KeyConverterInterface
{
    public function getName(string $fieldName): string
    {
        return strtolower($fieldName);
    }
}
