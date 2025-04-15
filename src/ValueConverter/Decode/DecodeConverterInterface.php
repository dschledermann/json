<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

interface DecodeConverterInterface
{
    /**
     * When decoding, convert a field.
     * You can use this to make transformations when encoding from JSON.
     *
     *
     * @param mixed $value The value of the field.
     * @return mixed The converted value.
     */
    public function convert(mixed $value): mixed;
}
