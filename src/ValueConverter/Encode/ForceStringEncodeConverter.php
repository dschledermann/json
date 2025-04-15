<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

use Attribute;

/**
 * Force the conversion of the field into a string.
 */
#[Attribute]
final class ForceStringEncodeConverter implements EncodeConverterInterface
{
    public function convert(mixed $value): mixed
    {
        return strval($value);
    }
}
