<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * Upper case the first letter.
 * This will effectively become Pascal case. This is common in C# code bases.
 */
#[Attribute]
final class UpperCaseFirst implements KeyConverterInterface
{
    public function getName(string $fieldName): string
    {
        return ucfirst($fieldName);
    }
}
