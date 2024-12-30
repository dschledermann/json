<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

use Attribute;

/**
 * Rename to this given value.
 * This key converter only makes sense for leaf fields.
 */
#[Attribute]
final class Rename implements KeyConverterInterface
{
    public function __construct(
        private string $intoName,
    ) {}

    public function getName(string $fieldName): string
    {
        return $this->intoName;
    }
}
