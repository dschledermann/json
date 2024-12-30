<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\AbstractChoice;
use Dschledermann\JsonCoder\Coder;
use Dschledermann\JsonCoder\KeyConverter\Rename;
use Dschledermann\JsonCoder\Filter\SkipNull;
use Dschledermann\JsonCoder\Filter\DoNotEncode;
use Dschledermann\JsonCoder\ValueConverter\Decode\AsIntDecoder;
use Dschledermann\JsonCoder\ValueConverter\Encode\ForceStringEncoder;
use Dschledermann\JsonCoder\ValueConverter\Encode\AsIntEncoder;
use PHPUnit\Framework\TestCase;

final class BarEnc
{
    public function __construct(
        public string $playerName,
        public int $height,
        public bool $minecraftPlayer,
        private string $someInternalField = "Eyuiwah7A",
    ) {}
}

final class FooEnc
{
    public function __construct(
        #[Rename("name")]
        public string $playerName,
        #[ForceStringEncoder]
        public int $height,
        #[AsIntEncoder, AsIntDecoder]
        public bool $minecraftPlayer,
        #[DoNotEncode]
        private string $someInternalField = "Eyuiwah7A",
    ) {}
}

#[SkipNull]
final class AdvancedEncPayload extends AbstractChoice
{
    public ?FooEnc $foo = null;
    public ?BarEnc $bar = null;
}

class AdvancedEncodeTest extends TestCase
{
    public function testAdvancedEncodings(): void
    {
        $coder = new Coder();

        // Baseline - if things are just encoded as-is, it would look like this
        $payload = AdvancedEncPayload::createFromVariant(new BarEnc('Mr. Hat', 179, true));
        $this->assertSame(
            '{"bar":{"playerName":"Mr. Hat","height":179,"minecraftPlayer":true,"someInternalField":"Eyuiwah7A"}}',
            $coder->encode($payload),
        );

        // If we have some special requirements, we can convert it into something like this
        $payload = AdvancedEncPayload::createFromVariant(new FooEnc('Mr. Hat', 179, true));

        $this->assertSame(
            '{"foo":{"name":"Mr. Hat","height":"179","minecraftPlayer":1}}',
            $coder->encode($payload),
        );
    }
}
