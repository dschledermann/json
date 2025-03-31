<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Coder;
use PHPUnit\Framework\TestCase;
use Tests\Dschledermann\JsonCoder\TestClasses\OuterAttributeType;
use Tests\Dschledermann\JsonCoder\TestClasses\Other\InnerAttributeType;

final class ArrayTypeDecodeWithAttributeTest extends TestCase
{
    public function testDecodingTypeOfArrayWithAttribute(): void
    {
        $coder = new Coder();

        /** @var OuterAttributeType */
        $value = $coder->decode('{"inner":1212,"innerList":[{"some":"hejsa","value":123},{"some":"Mummi","value":47}]}', OuterAttributeType::class);

        $this->assertNotNull($value);
        $this->assertSame(OuterAttributeType::class, get_class($value));
        $this->assertCount(2, $value->innerList);
        $this->assertSame(InnerAttributeType::class, get_class($value->innerList[0]));
    }
}
