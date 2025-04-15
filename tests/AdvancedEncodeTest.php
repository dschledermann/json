<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Encoder;
use Dschledermann\JsonCoder\AbstractChoice;
use Dschledermann\JsonCoder\KeyConverter\Rename;
use Dschledermann\JsonCoder\Filter\Encode\AllowEncode;
use Dschledermann\JsonCoder\Filter\Encode\SkipEncodeIfNull;
use Dschledermann\JsonCoder\Filter\Encode\SkipEncode;
use Dschledermann\JsonCoder\ValueConverter\Encode\AsIntEncodeConverter;
use Dschledermann\JsonCoder\ValueConverter\Encode\ForceStringEncodeConverter;
use Dschledermann\JsonCoder\VariantChoiceTrait;
use PHPUnit\Framework\TestCase;

class AdvancedEncodeTest extends TestCase
{
    public function testAdvancedEncodings(): void
    {
        $encoder = Encoder::create(AdvancedEncPayload::class);

        // Baseline - if things are just encoded as-is, it would look like this
        $payload = AdvancedEncPayload::createFromVariant(new BarEnc('Mr. Hat', 179, true));
        $this->assertSame(
            '{"bar":{"playerName":"Mr. Hat","height":179,"minecraftPlayer":true,"someInternalField":"Eyuiwah7A"}}',
            $encoder->encode($payload),
        );

        // If we have some special requirements, we can convert it into something like this
        $payload = AdvancedEncPayload::createFromVariant(new FooEnc('Mr. Hat', 179, true));

        $this->assertSame(
            '{"foo":{"name":"Mr. Hat","height":"179","minecraftPlayer":1}}',
            $encoder->encode($payload),
        );
    }
}

#[AllowEncode]
final class BarEnc
{
    public function __construct(
        public string $playerName,
        public int $height,
        public bool $minecraftPlayer,
        private string $someInternalField = "Eyuiwah7A",
    ) {}
}

#[AllowEncode]
final class FooEnc
{
    public function __construct(
        #[Rename("name")]
        public string $playerName,
        #[ForceStringEncodeConverter]
        public int $height,
        #[AsIntEncodeConverter]
        public bool $minecraftPlayer,
        #[SkipEncode]
        private string $someInternalField = "Eyuiwah7A",
    ) {}
}

#[SkipEncodeIfNull]
final class AdvancedEncPayload
{
    use VariantChoiceTrait;

    public ?FooEnc $foo = null;
    public ?BarEnc $bar = null;
}
