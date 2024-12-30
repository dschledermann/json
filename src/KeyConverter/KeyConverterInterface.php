<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

interface KeyConverterInterface
{
    /**
     * Convert the field name in the class structure to the field name in the JSON
     * stream.
     * It will alway convert from the property name in the class to the field name
     * in the JSON stream.
     */
    public function getName(string $fieldName): string;
}
