<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\Encoder;
use PHPUnit\Framework\TestCase;

class EvilTypesTest extends TestCase
{
    public function testEncodingWithoutType(): void
    {
        $this->expectExceptionMessage("[hei3Ahcio]");
        $encoder = Encoder::create(ClassWithTypelessField::class);
    }

    public function testDecodingWithoutType(): void
    {
        $this->expectExceptionMessage("[iKe7Jue9s]");
        $decoder = Decoder::create(ClassWithTypelessField::class);

    }
}

final class ClassWithTypelessField
{
    public string $fieldWithType;
    public $fieldWithOutType;
}
