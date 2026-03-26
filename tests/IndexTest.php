<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\Encoder;
use Dschledermann\JsonCoder\KeyConverter\ToSnakeCase;
use Dschledermann\JsonCoder\SquashIndexes;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testDecodingWithIndexSquash(): void
    {
        $list = '{"d43rfr":{"something":"Wow!"}}';
        $decoder = Decoder::create(TypeWithIndexSquash::class);
        $decodedList = $decoder->decodeArray($list);
        $this->assertEquals([0 => new TypeWithIndexSquash("Wow!")], $decodedList);
    }

    public function testEncodingWithIndexSquash(): void
    {
        $list = ["Hey" => new TypeWithIndexSquash("you"), "Who?" => new TypeWithIndexSquash("me")];
        $encoder = Encoder::create(TypeWithIndexSquash::class);
        $encodedList = $encoder->encodeArray($list);
        $this->assertEquals('[{"something":"you"},{"something":"me"}]', $encodedList);
    }

    public function testSublistDecodingWithAndWithoutIndexSquash(): void
    {
        $json = '{"n":123,"list":{"Hey":{"s":"go"},"You":{"s":"home"}},"otherList":{"Yo!":{"s":"to me"}}}';
        $decoder = Decoder::create(TypeWithSublist::class);
        $decoded = $decoder->decode($json);
        $this->assertEquals(
            new TypeWithSublist(
                123,
                [
                    new SubElement("go"),
                    new SubElement("home")],
                ["Yo!" => new SubElement("to me")],
            ),
            $decoded,
        );
    }

    public function testSublistEncodingWithAndWithoutIndexSquash(): void
    {
        $obj = new TypeWithSublist(
            123,
            [
                "a" => new SubElement("go"),
                "b" => new SubElement("home"),
            ],
            ["Wow!" => new SubElement("there")],
        );
        $encoder = Encoder::create(TypeWithSublist::class);
        $encoded = $encoder->encode($obj);

        $this->assertEquals(
            '{"n":123,"list":[{"s":"go"},{"s":"home"}],"otherList":{"Wow!":{"s":"there"}}}',
            $encoded,
        );
    }

    public function testEncodeSquashIndexOnPrimitiveType(): void
    {
        $primitiveList = ['a' => 'aa', 'b' => 'bb', 'c' => 123];
        $elementWithList = new ElementWithList("something", $primitiveList, $primitiveList);
        $encoder = Encoder::create(ElementWithList::class);
        $json = $encoder->encode($elementWithList);
        $this->assertEquals(
            '{"str":"something","list_a":["aa","bb",123],"list_b":{"a":"aa","b":"bb","c":123}}',
            $json,
        );

    }

    public function testDecodeSquashIndexOnPrimitiveType(): void
    {
        $json = '{"str":"else","list_a":{"a":"aa","b":"bb","c":123},"list_b":{"a":"aa","b":"bb","c":123}}';
        $decoder = Decoder::create(ElementWithList::class);
        $obj = $decoder->decode($json);

        $this->assertEquals(
            new ElementWithList(
                "else",
                ['aa', 'bb', 123],
                ['a' => 'aa', 'b' => 'bb', 'c' => 123],
            ),
            $obj,
        );
    }
}

#[SquashIndexes]
class TypeWithIndexSquash
{
    public function __construct(
        public string $something,
    ) {}
}

class TypeWithSublist
{
    public function __construct(
        public int $n,
        /** @var SubElement[] */
        #[SquashIndexes]
        public array $list,
        /** @var SubElement[] */
        public array $otherList,
    ) {}
}

class SubElement
{
    public function __construct(
        public string $s,
    ) {}
}

#[SquashIndexes]
final class Element
{
    public function __construct(
        public string $str,
    ) {}
}

#[ToSnakeCase]
final class ElementWithList
{
    public function __construct(
        public string $str,

        /** @var mixed[] */
        #[SquashIndexes]
        public array $listA,

        /** @var mixed[] */
        public array $listB,
    ) {}
}
