<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\CoderException;
use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\KeyConverter\CaseConverter;
use PHPUnit\Framework\TestCase;

class UnpackDummy
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        public UnpackSubObj $someSubObj,
    ) {}
}

#[CaseConverter("Snake")]
class SnakeDummy extends UnpackDummy
{}

class UnpackSubObj
{
    public function __construct(
        public float $value,
    ) {}
}

class AnotherDummy
{
    public function __construct(
        public ?string $optionalField,
        public string $requiredField,
    ) {}
}

class DecodeTest extends TestCase
{
    public function testBasicDecoding(): void
    {
        $decoder = Decoder::create(UnpackDummy::class);

        $this->assertEquals(
            new UnpackDummy(
                'Hej, hej, Dr. Pjuskebusk',
                1,
                new UnpackSubObj(1733.339149),
            ),
            $decoder->decode(
                '{"someFoo":"Hej, hej, Dr. Pjuskebusk","someBar":1,"someSubObj":{"value":1733.339149}}',
            ),
        );
    }

    public function testDecodingWithCaseConverter(): void
    {
        $decoder = Decoder::create(SnakeDummy::class);

        $this->assertEquals(
            new SnakeDummy(
                "Hej, hej, Martin og Ketil",
                666,
                new UnpackSubObj(3.14159265359),
            ),
            $decoder->decode(
                '{"some_foo":"Hej, hej, Martin og Ketil","some_bar":666,"some_sub_obj":{"value":3.14159265359}}',
            ),
        );
    }

    public function testDecodingArray(): void
    {
        $decoder = Decoder::create(UnpackSubObj::class);

        $this->assertEquals(
            [
                new UnpackSubObj(1.733345411),
                new UnpackSubObj(1.733345424),
                new UnpackSubObj(1.733345435),
            ],
            $decoder->decodeArray(
                '[{"value":1.733345411},{"value":1.733345424},{"value":1.733345435}]',
            ),
        );
    }

    public function testDecodingRequiredMissingField(): void
    {
        $this->expectException(CoderException::class);
        $this->expectExceptionMessage('[Aeghai9ja]');

        $decoder = Decoder::create(AnotherDummy::class);
        $src = '{"optionalField":"I am here"}';
        $decoder->decode($src);
    }

    public function testDecodingOptionalMissingField(): void
    {
        $decoder = Decoder::create(AnotherDummy::class);
        $src = '{"requiredField":"I am here"}';
        $result = $decoder->decode($src);

        $this->assertNull($result->optionalField);
        $this->assertEquals("I am here", $result->requiredField);
    }
}
