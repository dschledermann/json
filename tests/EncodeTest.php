<?php

declare(strict_types=1);

namespace Tests\Dschledermann\Json;

use Dschledermann\Json\JsonEncoder;
use Jawira\CaseConverter\Convert;
use PHPUnit\Framework\TestCase;

final class SomeDummy
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        public SomeSubObj $someSubObj,
    ) {}
}

final class SomeSubObj
{
    public function __construct(
        public float $value,
    ) {}
}

class EncodeTest extends TestCase
{
    public function testBasicEncoding(): void
    {
        $obj = new SomeDummy(
            "Hej, hej, Dr. Pjuskebusk",
            1,
            new SomeSubObj(1733.339149),
        );

        $encoder = new JsonEncoder();

        $this->assertSame(
            '{"someFoo":"Hej, hej, Dr. Pjuskebusk","someBar":1,"someSubObj":{"value":1733.339149}}',
            $encoder->encode($obj),
        );
    }

    public function testEncodingWithCaseConverter(): void
    {
        $obj = new SomeDummy(
            "Hej, hej, Martin og Ketil",
            666,
            new SomeSubObj(3.14159265359),
        );

        $encoder = new JsonEncoder(
            function(string $from): string {
                return strtoupper($from);
            }
        );

        $this->assertSame(
            '{"SOMEFOO":"Hej, hej, Martin og Ketil","SOMEBAR":666,"SOMESUBOBJ":{"VALUE":3.14159265359}}',
            $encoder->encode($obj),
        );

        $encoder = new JsonEncoder(
            function(string $from): string {
                $convert = new Convert($from);
                return $convert->fromCamel()->toSnake();
            }
        );

        $this->assertSame(
            '{"some_foo":"Hej, hej, Martin og Ketil","some_bar":666,"some_sub_obj":{"value":3.14159265359}}',
            $encoder->encode($obj),
        );
    }

    public function testEncodingAnArray(): void
    {
        $list = [
            'pi' => new SomeSubObj(3.14159265359),
            'e' => new SomeSubObj(2.71828182846),
            'sqr2' => new SomeSubObj(1.41421356237),
        ];

        $encoder = new JsonEncoder();
        $this->assertEquals(
            '{"pi":{"value":3.14159265359},"e":{"value":2.71828182846},"sqr2":{"value":1.41421356237}}',
            $encoder->encodeArray($list),
        );
    }
}
