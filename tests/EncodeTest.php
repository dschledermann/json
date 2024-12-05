<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Coder;
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

final class ObjWithInternalArray
{
    public function __construct(
        public string $name,
        public array $objs,
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
        $obj = new SomeDummy(
            "Hej, hej, Martin og Ketil",
            666,
            new SomeSubObj(3.14159265359),
        );

        $encoder = (new Coder())->withKeyCaseConverter(fn ($s) => strtoupper($s));

        $this->assertSame(
            '{"SOMEFOO":"Hej, hej, Martin og Ketil","SOMEBAR":666,"SOMESUBOBJ":{"VALUE":3.14159265359}}',
            $encoder->encode($obj),
        );

        $encoder = (new Coder())->withKeyCaseConverter(
            function(string $from): string {
                $convert = new Convert($from);
                return $convert->toSnake();
            }
        );

        $this->assertSame(
            '{"some_foo":"Hej, hej, Martin og Ketil","some_bar":666,"some_sub_obj":{"value":3.14159265359}}',
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
        $encoder = (new Coder())->withKeyCaseConverter(fn ($s) => strtoupper($s));

        $obj = new ObjWithInternalArray(
            'Skeletor',
            [
                new SomeSubObj(77.2134),
                new SomeSubObj(1212.12),
            ],
        );

        $this->assertEquals(
            '{"NAME":"Skeletor","OBJS":[{"VALUE":77.2134},{"VALUE":1212.12}]}',
            $encoder->encode($obj),
        );
    }

    public function testNestedArrayWithSimpleValues(): void
    {
        $obj = new ObjWithInternalArray(
            'Heman',
            [
                'Julie, keep this with you, and Eternia will always be near.',
                'People of Eternia, the war is over.',
                'By the power of Greyskull!',
            ]
        );

        $coder = (new Coder())->withEncodeFlags(JSON_PRETTY_PRINT);

        $output = <<<END
{
    "name": "Heman",
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
