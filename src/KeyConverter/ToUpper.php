<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * Uppercase the key
 */
#[Attribute]
final class ToUpper implements KeyConverterInterface
{
    public function getName(string $fieldName): string
    {
        return strtoupper($fieldName);
    }
}
