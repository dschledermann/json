<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter\Encode;

interface EncodeFilterInterface
{
    /**
     * Tell if a property with given name and value should be encoded
     * @param string $propertyName    Field in question
     * @param mixed  $value           Value of field
     *
     * @return bool                   Should this field be decoded
     */
    public function doEncode(string $propertyName, mixed $value): bool;
}
