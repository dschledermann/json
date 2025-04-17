<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\Encoder;
use PHPUnit\Framework\TestCase;

class EvilArraysTest extends TestCase
{
    public function testBrokenDocBlock(): void
    {
        $this->expectExceptionMessage("[xuequ9Fee]");
        $encoder = Encoder::create(BrokenDocBlock::class);
    }

    public function testMissingDocBlock(): void
    {
        $this->expectExceptionMessage("[ieyah4Ahp]");
        $decoder = Decoder::create(MissingDocBlock::class);
    }
}

class BrokenDocBlock
{
    private string $man;
    /** @var mememe ... */
    private array $evilField;
}

class MissingDocBlock
{
    private string $man;
    private array $evilField;
}
