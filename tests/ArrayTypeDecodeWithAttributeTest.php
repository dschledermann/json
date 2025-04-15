<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use PHPUnit\Framework\TestCase;
use Tests\Dschledermann\JsonCoder\TestClasses\OuterAttributeType;
use Tests\Dschledermann\JsonCoder\TestClasses\Other\InnerTypeInDifferentNamespace;

final class ArrayTypeDecodeWithAttributeTest extends TestCase
{
    public function testDecodingTypeOfArrayWithAttribute(): void
    {
        $coder = Decoder::create(OuterAttributeType::class);

        $value = $coder->decode('{"inner":1212,"innerList":[{"some":"hejsa","value":123},{"some":"Mummi","value":47}]}');

        $this->assertNotNull($value);
        $this->assertSame(OuterAttributeType::class, get_class($value));
        $this->assertCount(2, $value->innerList);
        $this->assertSame(InnerTypeInDifferentNamespace::class, get_class($value->innerList[0]));
    }
}
