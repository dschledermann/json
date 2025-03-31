<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder\TestClasses;

final class OuterHintType
{
    /** @var InnerListType[] */
    public array $firstInnerList;

    /** @var array<InnerArrayShapeType> */
    public array $secondInnerList;
    
    public string $value;
    public SomethingElse $somethingElse;
}
