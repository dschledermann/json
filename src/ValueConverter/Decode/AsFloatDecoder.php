<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

use Attribute;

#[Attribute]
final class AsFloatDecoder implements DecoderInterface
{
    public function decode(mixed $value): mixed
    {
        return floatval($value);
    }
}
