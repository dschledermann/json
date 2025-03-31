<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Coder;
use PHPUnit\Framework\TestCase;
use Tests\Dschledermann\JsonCoder\TestClasses\OuterWithSimpleListValues;

final class ArrayTypeDecodeSimpleListTest extends TestCase
{
    public function testDecodingClassWithSimpleValues(): void
    {
        $json = '{"listOfInts":[23,12,34,6,7,812,12]}';

        $coder = new Coder();
        $value = $coder->decode($json, OuterWithSimpleListValues::class);

        $this->assertSame(OuterWithSimpleListValues::class, get_class($value));
        $this->assertCount(7, $value->listOfInts);
    }

    public function testDecodingClassWithSimpleButWrongValues(): void
    {
        $this->expectExceptionMessage("[zahShah6t]");
        $json = '{"listOfInts":[23,"dssdfdfs",34,6,7,812,12]}';

        $coder = new Coder();
        $value = $coder->decode($json, OuterWithSimpleListValues::class);

        $this->assertSame(OuterWithSimpleListValues::class, get_class($value));
        $this->assertCount(7, $value->listOfInts);
    }
}
