<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

use Attribute;

/**
 * Make sure that the field is treated as an int.
 */
#[Attribute]
final class AsIntEncoder implements EncoderInterface
{
    public function encode(mixed $value): mixed
    {
        return intval($value);
    }
}
