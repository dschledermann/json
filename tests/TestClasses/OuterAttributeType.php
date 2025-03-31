<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder\TestClasses;

use Dschledermann\JsonCoder\ListType;
use Tests\Dschledermann\JsonCoder\TestClasses\Other\InnerTypeInDifferentNamespace;

final class OuterAttributeType
{
    public int $inner;

    #[ListType(InnerTypeInDifferentNamespace::class)]
    public array $innerList;
}
