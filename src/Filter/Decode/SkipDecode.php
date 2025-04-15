<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter\Decode;

use Attribute;

#[Attribute]
final class SkipDecode implements DecodeFilterInterface
{
    public function doDecode(string $propertyName): bool
    {
        return false;
    }
}
