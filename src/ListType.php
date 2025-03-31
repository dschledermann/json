<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Attribute;

#[Attribute]
final class ListType
{
    private bool $simpleType = true;
    private string $typeName;
    
    public function __construct(string $typeName, string $namespace = '')
    {
        if (in_array(
            $typeName,
            [
                'bool',
                'boolean',
                'string',
                'int',
                'integer',
                'float',
                'double',
            ],
        )) {
            $this->simpleType = true;
            $this->typeName = match ($typeName) {
                'bool' => 'boolean',
                'int' => 'integer',
                'float' => 'double',
                default => $typeName,
            };
        } else {
            $this->simpleType = false;
            if ($namespace) {
                $this->typeName = $namespace . "\\" . $typeName;
            } else {
                $this->typeName = $typeName;
            }
        }
    }

    public function isSimpleType(): bool
    {
        return $this->simpleType;
    }

    public function getType(): string
    {
        return $this->typeName;
    }
}
