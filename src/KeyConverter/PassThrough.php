<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * This is used as the default/fallback key converter
 * It can also be used to turn off key conversion in nested structures.
 */
#[Attribute]
final class PassThrough implements KeyConverterInterface
{
    public function getName(string $fieldName): string
    {
        return $fieldName;
    }
}
