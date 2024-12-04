<?php

declare(strict_types=1);

namespace Tests\Dschledermann\Json;

use Dschledermann\Json\JsonDecoder;
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
        $src = '{"someFoo":"Hej, hej, Dr. Pjuskebusk","someBar":1,"someSubObj":{"value":1733.339149}}';

        $decoder = new JsonDecoder();
        $this->assertEquals(
            new UnpackDummy(
                'Hej, hej, Dr. Pjuskebusk',
                1,
                new UnpackSubObj(1733.339149),
            ),
            $decoder->decode($src, UnpackDummy::class),
        );
    }

    public function testDecodingWithCaseConverter(): void
    {
        $src = '{"some_foo":"Hej, hej, Martin og Ketil","some_bar":666,"some_sub_obj":{"value":3.14159265359}}';

        $decoder = new JsonDecoder(function(string $key): string {
            return (new Convert($key))->fromCamel()->toSnake();
        });

        $this->assertEquals(
            new UnpackDummy(
                "Hej, hej, Martin og Ketil",
                666,
                new UnpackSubObj(3.14159265359),
            ),
            $decoder->decode($src, UnpackDummy::class),
        );
    }

    public function testDecodingArray(): void
    {
        $src = '[{"value":1.733345411},{"value":1.733345424},{"value":1.733345435}]';

        $decoder = new JsonDecoder();

        $this->assertEquals(
            [
                new UnpackSubObj(1.733345411),
                new UnpackSubObj(1.733345424),
                new UnpackSubObj(1.733345435),
            ],
            $decoder->decodeArray($src, UnpackSubObj::class),
        );
    }
}


    
