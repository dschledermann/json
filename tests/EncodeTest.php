<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Encoder;
use Dschledermann\JsonCoder\Filter\Encode\SkipEncodeIfNull;
use Dschledermann\JsonCoder\KeyConverter\ToUpper;
use Dschledermann\JsonCoder\KeyConverter\ToLower;
use PHPUnit\Framework\TestCase;

class EncodeTest extends TestCase
{
    public function testBasicEncoding(): void
    {
        $encoder = Encoder::create(SomeDummy::class);
        $this->assertSame(
            '{"someFoo":"Hej, hej, Dr. Pjuskebusk","someBar":1,"someSubObj":{"value":1733.339149}}',
            $encoder->encode(new SomeDummy(
                "Hej, hej, Dr. Pjuskebusk",
                1,
                new SomeSubObj(1733.339149),
            )),
        );
    }

    public function testEncodingWithCaseConverter(): void
    {
        $encoder = Encoder::create(SomeDummyUpper::class);

        $obj = new SomeDummyUpper(
            "Hej, hej, Martin og Ketil",
            666,
            new SomeSubObj(3.14159265359),
        );

        $this->assertSame(
            '{"SOMEFOO":"Hej, hej, Martin og Ketil","SOMEBAR":666,"SOMESUBOBJ":{"VALUE":3.14159265359}}',
            $encoder->encode($obj),
        );

        $encoder = Encoder::create(SomeDummyFieldLower::class);

        $obj = new SomeDummyFieldLower(
            "Hej, hej, Martin og Ketil",
            666,
            new SomeSubObj(3.14159265359),
        );

        $this->assertSame(
            '{"someFoo":"Hej, hej, Martin og Ketil","someBar":666,"somesubobj":{"value":3.14159265359}}',
            $encoder->encode($obj),
        );
    }

    public function testEncodingAnArray(): void
    {
        $encoder = Encoder::create(SomeSubObj::class);
        $this->assertEquals(
            '[{"value":3.14159265359},{"value":2.71828182846},{"value":1.41421356237}]',
            $encoder->encodeArray([
                new SomeSubObj(3.14159265359),
                new SomeSubObj(2.71828182846),
                new SomeSubObj(1.41421356237),
            ]),
        );
    }

    public function testNestedArray(): void
    {
        $encoder = Encoder::create(ObjWithInternalObjsArray::class);

        $obj = new ObjWithInternalObjsArray(
            'Skeletor',
            [
                new SomeSubObj(1.85),
                new SomeSubObj(97.1),
            ],
        );

        $this->assertEquals(
            '{"name":"Skeletor","objs":[{"value":1.85},{"value":97.1}]}',
            $encoder->encode($obj),
        );
    }

    public function testNullability(): void
    {
        $coder = Encoder::create(ObjWithNullableKeep::class);
        $obj = new ObjWithNullableKeep("Teela");
        $this->assertEquals('{"someField":"Teela","nullableField":null}', $coder->encode($obj));

        $coder = Encoder::create(ObjWithNullableSkip::class);
        $obj = new ObjWithNullableSkip("Teela");
        $this->assertEquals('{"someField":"Teela"}', $coder->encode($obj));
    }

    public function testNestedArrayWithSimpleValues(): void
    {
        $encoder = Encoder::create(ObjWithInternalStringArray::class, JSON_PRETTY_PRINT);
        $obj = new ObjWithInternalStringArray(
            'He-man',
            [
                'Julie, keep this with you, and Eternia will always be near.',
                'People of Eternia, the war is over.',
                'By the power of Greyskull!',
            ]
        );

        $output = <<<END
{
    "name": "He-man",
    "strings": [
        "Julie, keep this with you, and Eternia will always be near.",
        "People of Eternia, the war is over.",
        "By the power of Greyskull!"
    ]
}
END;
        $this->assertEquals($output, $encoder->encode($obj));
    }
}

final class SomeDummy
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        public SomeSubObj $someSubObj,
    ) {}
}

#[ToUpper]
final class SomeDummyUpper
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        public SomeSubObj $someSubObj,
    ) {}
}

final class SomeDummyFieldLower
{
    public function __construct(
        public string $someFoo,
        public int $someBar,
        #[ToLower]
        public SomeSubObj $someSubObj,
    ) {}
}

final class SomeSubObj
{
    public function __construct(
        public float $value,
    ) {}
}

final class ObjWithInternalStringArray
{
    public function __construct(
        public string $name,
        /** @var string[] */
        public array $strings,
    ) {}
}

final class ObjWithInternalObjsArray
{
    public function __construct(
        public string $name,
        /** @var SomeSubObj[] */
        public array $objs,
    ) {}
}


final class ObjWithNullableKeep
{
    public function __construct(
        public string $someField,
        public ?string $nullableField = null,
    ) {}
}

#[SkipEncodeIfNull]
final class ObjWithNullableSkip
{
    public function __construct(
        public string $someField,
        public ?string $nullableField = null,
    ) {}
}
