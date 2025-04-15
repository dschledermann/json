<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

interface EncodeConverterInterface
{
    /**
     * When encoding, convert a field.
     * You can use this to make transformations when encoding into JSON.
     *
     * @param mixed $value The value of the field.
     * @return mixed The converted value.
     */
    public function convert(mixed $value): mixed;
}
