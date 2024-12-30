<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

use Attribute;

#[Attribute]
final class AsIntDecoder implements DecoderInterface
{
    public function decode(mixed $value): mixed
    {
        return intval($value);
    }
}
