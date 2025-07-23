<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

use Attribute;

#[Attribute]
final class AsIntDecodeConverter implements DecodeConverterInterface
{
    public function decodeTo(mixed $value): mixed
    {
        return intval($value);
    }
}
