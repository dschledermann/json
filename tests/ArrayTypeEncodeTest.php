<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Encoder;
use Dschledermann\JsonCoder\ValueConverter\Encode\AsIntEncodeConverter;
use PHPUnit\Framework\TestCase;

class ArrayTypeEncodeTest extends TestCase
{
    public function testConvertedArrayElements(): void
    {
        $element = new TypeWithConvertedArrayElements(
            "FooBar",
            [ 12.0, 12.2, 3.15]
        );

        $encoder = Encoder::create(TypeWithConvertedArrayElements::class);

        $this->assertSame(
            '{"field":"FooBar","arr":[12,12,3]}',
            $encoder->encode($element),
        );
    }
}


final class TypeWithConvertedArrayElements
{
    public function __construct(
        public string $field,

        /** @var float[] */
        #[AsIntEncodeConverter]
        public array $arr,
    ) {}
}
