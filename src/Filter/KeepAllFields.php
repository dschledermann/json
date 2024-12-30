<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter;

use Attribute;

#[Attribute]
final class KeepAllFields implements FilterInterface
{
    public function doEncodeField(string $name, mixed $value): bool
    {
        return true;
    }
}
