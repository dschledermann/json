<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

final class TypeCategory
{
    private function __construct(
        private bool $simpleType,
        private string $typeName,
    ) {}

    public static function create(
        string $typeName,
        string $namespaceName = '',
    ): TypeCategory
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
            return new TypeCategory(
                true,
                match ($typeName) {
                    'bool' => 'boolean',
                    'int' => 'integer',
                    'float' => 'double',
                    default => $typeName,
                }
            );
        } else {
            // Is it a full path?
            if (substr($typeName, 0, 1) === '\\') {
                if (class_exists($typeName)) {
                    return new TypeCategory(false, $typeName);
                }

                throw new CoderException(sprintf(
                    "[OH6engei7] class %s not found",
                    $typeName,
                ));
            }

            // Does it work with the namespaceName appended?
            $typeName = $namespaceName . "\\" . $typeName;
            if (class_exists($typeName)) {
                return new TypeCategory(false, $typeName);
            }

            throw new CoderException(sprintf(
                "[EeghaeL8J] class %s not found",
                $typeName,
            ));
        }
    }

    public function isSimpleType(): bool
    {
        return $this->simpleType;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }
}
