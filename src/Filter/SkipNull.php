<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter;

use Attribute;

/**
 * This filter is useful as a toplevel filter to prevent the encoding of fields
 * that are set to null.
 */
#[Attribute]
final class SkipNull implements FilterInterface
{
    public function doEncodeField(string $name, mixed $value): bool
    {
        return !is_null($value);
    }
}
