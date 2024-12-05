<?php

declare(strict_types=1);

namespace Tests\Dschledermann\Json;

use Dschledermann\JsonCoder\Coder;
use Jawira\CaseConverter\Convert;
use PHPUnit\Framework\TestCase;

final class UnpackDummy
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        public UnpackSubObj $someSubObj,
    ) {}
}

final class UnpackSubObj
{
    public function __construct(
        public float $value,
    ) {}
}

class DecodeTest extends TestCase
{
    public function testBasicDecoding(): void
    {
        $decoder = new Coder();

        $this->assertEquals(
            new UnpackDummy(
                'Hej, hej, Dr. Pjuskebusk',
                1,
                new UnpackSubObj(1733.339149),
            ),
            $decoder->decode(
                '{"someFoo":"Hej, hej, Dr. Pjuskebusk","someBar":1,"someSubObj":{"value":1733.339149}}',
                UnpackDummy::class,
            ),
        );
    }

    public function testDecodingWithCaseConverter(): void
    {
        $decoder = (new Coder())->withKeyCaseConverter(function(string $key): string {
            return (new Convert($key))->fromCamel()->toSnake();
        });

        $this->assertEquals(
            new UnpackDummy(
                "Hej, hej, Martin og Ketil",
                666,
                new UnpackSubObj(3.14159265359),
            ),
            $decoder->decode(
                '{"some_foo":"Hej, hej, Martin og Ketil","some_bar":666,"some_sub_obj":{"value":3.14159265359}}',
                UnpackDummy::class,
            ),
        );
    }

    public function testDecodingArray(): void
    {
        $decoder = new Coder();

        $this->assertEquals(
            [
                new UnpackSubObj(1.733345411),
                new UnpackSubObj(1.733345424),
                new UnpackSubObj(1.733345435),
            ],
            $decoder->decodeArray(
                '[{"value":1.733345411},{"value":1.733345424},{"value":1.733345435}]',
                UnpackSubObj::class,
            ),
        );
    }
}


    
