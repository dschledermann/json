<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use DateTimeImmutable;
use Dschledermann\JsonCoder\Decoder;
use Dschledermann\JsonCoder\Encoder;
use Dschledermann\JsonCoder\ValueConverter\DateTimeImmutableValueConverter;
use PHPUnit\Framework\TestCase;

class DateTimeImmutableValueConverterTest extends TestCase
{
    public function testEncodingDateTimeImmutable(): void
    {
        $encoder = Encoder::create(WithSomeDateTime::class);

        $obj = new WithSomeDateTime(
            new DateTimeImmutable('@1753258103'),
            'Some String',
        );

        $this->assertSame(
            '{"ts":"2025-07-23T08:08:23+00:00","someString":"Some String"}',
            $encoder->encode($obj),
        );
    }

    public function testDecodingDateTimeImmutable(): void
    {
        $decoder = Decoder::create(WithSomeDateTime::class);
        $str = '{"ts":"2025-07-23T08:08:23+00:00","someString":"Some String"}';
        $obj = $decoder->decode($str);

        $this->assertEquals(new DateTimeImmutable('@1753258103'), $obj->ts);
    }
}

final class WithSomeDateTime
{
    public function __construct(
        #[DateTimeImmutableValueConverter]
        public DateTimeImmutable $ts,
        public string $someString,
    ) {}
}
