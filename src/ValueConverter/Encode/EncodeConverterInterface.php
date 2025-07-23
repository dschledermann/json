<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

interface EncodeConverterInterface
{
    /**
     * When encoding into JSON, convert a field.
     * You can use this to to transformations when reading from your PHP type into JSON.
     *
     * @param   mixed   $value   The value of the field.
     * @return  mixed            The converted value.
     */
    public function encodeTo(mixed $value): mixed;
}
