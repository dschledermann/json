<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

use Attribute;

/**
 * Force the conversion of the field into a string.
 */
#[Attribute]
final class ForceStringEncoder implements EncoderInterface
{
    public function encode(mixed $value): mixed
    {
        return strval($value);
    }
}
