<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * Convert the field name to snake case.
 * This is a common naming format if you are interfacing with Rust or C code bases.
 */
#[Attribute]
final class ToSnakeCase implements KeyConverterInterface
{
    public function getName(string $fieldName): string
    {
        return ltrim(strtolower(preg_replace('/([A-Z])/', '_\\1', $fieldName)), '_');
    }
}
