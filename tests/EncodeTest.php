<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Coder;
use Dschledermann\JsonCoder\KeyConverter\ToUpper;
use Dschledermann\JsonCoder\Filter\SkipNull;
use Dschledermann\JsonCoder\KeyConverter\ToLower;
use PHPUnit\Framework\TestCase;

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

final class ObjWithInternalArray
{
    public function __construct(
        public string $name,
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

#[SkipNull]
final class ObjWithNullableSkip
{
    public function __construct(
        public string $someField,
        public ?string $nullableField = null,
    ) {}
}


class EncodeTest extends TestCase
{
    public function testBasicEncoding(): void
    {
        $encoder = new Coder();
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
        $encoder = new Coder();

        $obj = new SomeDummyUpper(
            "Hej, hej, Martin og Ketil",
            666,
            new SomeSubObj(3.14159265359),
        );

        $this->assertSame(
            '{"SOMEFOO":"Hej, hej, Martin og Ketil","SOMEBAR":666,"SOMESUBOBJ":{"VALUE":3.14159265359}}',
            $encoder->encode($obj),
        );

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
        $encoder = new Coder();
        $this->assertEquals(
            '{"pi":{"value":3.14159265359},"e":{"value":2.71828182846},"sqr2":{"value":1.41421356237}}',
            $encoder->encodeArray([
                'pi' => new SomeSubObj(3.14159265359),
                'e' => new SomeSubObj(2.71828182846),
                'sqr2' => new SomeSubObj(1.41421356237),
            ]),
        );
    }

    public function testNestedArray(): void
    {
        $encoder = new Coder();

        $obj = new ObjWithInternalArray(
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
        $coder = new Coder();        

        $obj = new ObjWithNullableKeep("Teela");
        $this->assertEquals('{"someField":"Teela","nullableField":null}', $coder->encode($obj));

        $obj = new ObjWithNullableSkip("Teela");
        $this->assertEquals('{"someField":"Teela"}', $coder->encode($obj));
    }

    public function testNestedArrayWithSimpleValues(): void
    {
        $obj = new ObjWithInternalArray(
            'He-man',
            [
                'Julie, keep this with you, and Eternia will always be near.',
                'People of Eternia, the war is over.',
                'By the power of Greyskull!',
            ]
        );

        $coder = (new Coder())->withEncodeFlags(JSON_PRETTY_PRINT);

        $output = <<<END
{
    "name": "He-man",
    "objs": [
        "Julie, keep this with you, and Eternia will always be near.",
        "People of Eternia, the war is over.",
        "By the power of Greyskull!"
    ]
}
END;
        $this->assertEquals($output,$coder->encode($obj));
    }
}
