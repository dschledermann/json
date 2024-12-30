<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\Filter;

interface FilterInterface
{
    /**
     * Tell if field with name $name and value $value should excluded from encoding.
     */
    public function doEncodeField(string $name, mixed $value): bool;
}

