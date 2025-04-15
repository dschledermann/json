<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use PHPUnit\Framework\TestCase;
use Tests\Dschledermann\JsonCoder\TestClasses\OuterWithSimpleListValues;

final class ArrayTypeDecodeSimpleListTest extends TestCase
{
    public function testDecodingClassWithSimpleValues(): void
    {
        $json = '{"listOfInts":[23,12,34,6,7,812,12]}';

        $coder = Decoder::create(OuterWithSimpleListValues::class);
        $value = $coder->decode($json);

        $this->assertSame(OuterWithSimpleListValues::class, get_class($value));
        $this->assertCount(7, $value->listOfInts);
    }

    public function testDecodingClassWithSimpleButWrongValues(): void
    {
        $this->expectExceptionMessage("[Jae9ac9ai]");
        $json = '{"listOfInts":[23,"dssdfdfs",34,6,7,812,12]}';

        $coder = Decoder::create(OuterWithSimpleListValues::class);
        $value = $coder->decode($json);

        $this->assertSame(OuterWithSimpleListValues::class, get_class($value));
        $this->assertCount(7, $value->listOfInts);
    }
}
