<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

use Attribute;

#[Attribute]
final class AsFloatDecodeConverter implements DecodeConverterInterface
{
    public function decodeTo(mixed $value): mixed
    {
        return floatval($value);
    }
}
