<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter\Encode;

interface EncoderInterface
{
    /**
     * When encoding, convert a field
     * @param mixed $value The value of the field.
     * @return mixed The converted value.
     */
    public function encode(mixed $value): mixed;
}
