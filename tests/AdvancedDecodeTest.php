<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\KeyConverter\ToUpper;
use Dschledermann\JsonCoder\KeyConverter\Rename;
use Dschledermann\JsonCoder\ValueConverter\Decode\AsFloatDecodeConverter;
use Dschledermann\JsonCoder\ValueConverter\Decode\AsIntDecodeConverter;
use Dschledermann\JsonCoder\VariantChoiceTrait;
use PHPUnit\Framework\TestCase;

class AdvancedDecodeTest extends TestCase
{
    public function testAdvancedDecoding(): void
    {
        $decoder = Decoder::create(AdvancedDecPayload::class);

        // Baseline - healthy JSON should look like this
        $src = '{"foo":{"someString":"Davs","someInt":123,"someFloat":123.123}}';
        $payload = $decoder->decode($src);

        $this->assertNotNull($payload);
        $this->assertNull($payload->bar);
        $this->assertNotNull($payload->foo);
        $this->assertSame("Davs", $payload->foo->someString);
        $this->assertSame(123, $payload->foo->someInt);
        $this->assertSame(123.123, $payload->foo->someFloat);

        // Or we can accept broken like this
        $src = '{"bar":{"SOMESTRING":"Davs","INT":"123-nah-nah-nah","FLOAT":"123.123-whoo-whoo"}}';
        $payload = $decoder->decode($src);

        $this->assertNotNull($payload);
        $this->assertNull($payload->foo);
        $this->assertNotNull($payload->bar);
        $this->assertSame("Davs", $payload->bar->someString);
        $this->assertSame(123, $payload->bar->someInt);
        $this->assertSame(123.123, $payload->bar->someFloat);
    }
}

final class FooDec
{
    public function __construct(
        public string $someString,
        public int $someInt,
        public float $someFloat,
    ) {}
}

final class BarDec
{
    public function __construct(
        #[ToUpper]
        public string $someString,
        #[AsIntDecodeConverter, Rename("INT")]
        public int $someInt,
        #[AsFloatDecodeConverter, Rename("FLOAT")]
        public float $someFloat,
    ) {}
}

final class AdvancedDecPayload
{
    use VariantChoiceTrait;
    public ?FooDec $foo = null;
    public ?BarDec $bar = null;
}
