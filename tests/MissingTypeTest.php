<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\Encoder;
use PHPUnit\Framework\TestCase;

class MissingTypeTest extends TestCase
{
    public function testMissingEncodeClass(): void
    {
        $this->expectExceptionMessage("[phuo9Coh9]");
        $encoder = Encoder::create(NonExisting::class);
    }

    public function testMissingDecodeClass(): void
    {
        $this->expectExceptionMessage("[Aet7ush7e]");
        $decoder = Decoder::create(NonExisting::class);
    }

    public function testMissingInnerClassEncode(): void
    {
        $this->expectExceptionMessage("[ohneeNg9y]");
        $encoder = Encoder::create(ClassWithMissingInnerClass::class);
    }

    public function testMissingInnerClassDecode(): void
    {
        $this->expectExceptionMessage("[ieWohf4ba]");
        $decoder = Decoder::create(ClassWithMissingInnerClass::class);
    }

}

class ClassWithMissingInnerClass
{
    public NonExisting $evil666;
}
