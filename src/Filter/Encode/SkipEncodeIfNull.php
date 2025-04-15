<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter\Encode;

use Attribute;

#[Attribute]
final class SkipEncodeIfNull implements EncodeFilterInterface
{
    public function doEncode(string $propertyName, mixed $value): bool
    {
        return !is_null($value);
    }
}
