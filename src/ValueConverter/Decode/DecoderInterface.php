<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Decode;

interface DecoderInterface
{
    /**
     * When decoding, convert a field.
     * @param mixed $value The value of the field.
     * @return mixed The converted value.
     */
    public function decode(mixed $value): mixed;
}
