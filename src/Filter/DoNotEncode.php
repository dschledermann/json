<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter;

use Attribute;

/**
 * This filter is useful as a leaf filter to prevent the encoding of a specific
 * property on the class.
 */
#[Attribute]
final class DoNotEncode implements FilterInterface
{
    public function doEncodeField(string $name, mixed $value): bool
    {
        return false;
    }
}
