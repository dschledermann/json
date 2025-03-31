<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Attribute;

#[Attribute]
final class ListType
{
    private string $className;
    
    public function __construct(string $className)
    {
        if (!class_exists($className)) {
            throw new CoderException(sprintf(
                "[Ahph5ahba] class %s was not found",
                $className,
            ));
        }
        $this->className = $className;
    }

    public function getType(): string
    {
        return $this->className;
    }
}
