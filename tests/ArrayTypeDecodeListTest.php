<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Coder;
use PHPUnit\Framework\TestCase;
use Tests\Dschledermann\JsonCoder\TestClasses\InnerArrayShapeType;
use Tests\Dschledermann\JsonCoder\TestClasses\InnerListType;
use Tests\Dschledermann\JsonCoder\TestClasses\OuterHintType;

final class ArrayTypeDecodeListTest extends TestCase
{
    public function testDecodingTypeOfArrayWithListAndArrayShape(): void
    {
        $coder = new Coder();

        /** @var OuterHintType */
        $value = $coder->decode('{"firstInnerList":[{"some":"hejsa","value":123},{"some":"Mummi","value":47}],"value":"davs","somethingElse":{"inner":666},"secondInnerList":[{"awesome":"Dude!"}]}', OuterHintType::class);

        $this->assertNotNull($value);
        $this->assertSame(OuterHintType::class, get_class($value));
        $this->assertCount(2, $value->firstInnerList);
        $this->assertSame(InnerListType::class, get_class($value->firstInnerList[0]));
        $this->assertCount(1, $value->secondInnerList);
        $this->assertSame(InnerArrayShapeType::class, get_class($value->secondInnerList[0]));
    }
}
