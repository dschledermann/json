<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\KeyConverter;

interface KeyConverterInterface
{
    /**
     * Convert the field name in the class structure to the field name in the JSON
     * stream. It will alway convert from the property name in the class to the field
     * name in the JSON stream.
     * The converters in this package are coded with the assumption that the PHP code
     * is named according to PSR-12.
     * If you need another format, just make a new class that implements this
     * interface, mark as an Attribute and use it on your data classes.
     *
     * @param string  $fieldName   Name in the PHP class.
     * @return string              Name in the JSON stream.
     */
    public function getName(string $fieldName): string;
}
