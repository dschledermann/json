<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter\Decode;

interface DecodeFilterInterface
{
    /**
     * Tell if a property with given name and value should be decoded
     * @param string $propertyName    Field in question
     *
     * @return bool                   Should this field be decoded
     */
    public function doDecode(string $propertyName): bool;
}
