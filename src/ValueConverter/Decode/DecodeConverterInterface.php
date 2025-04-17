<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

interface DecodeConverterInterface
{
    /**
     * When decoding from JSON, convert a field.
     * You can use this to do transformations when reading from JSON into your PHP type.
     *
     * @param   mixed   $value   The value of the field.
     * @return  mixed            The converted value.
     */
    public function convert(mixed $value): mixed;
}
