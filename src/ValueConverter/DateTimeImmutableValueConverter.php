<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder\ValueConverter;

use Attribute;
use DateTimeImmutable;
use Dschledermann\JsonCoder\ValueConverter\Decode\DecodeConverterInterface;
use Dschledermann\JsonCoder\ValueConverter\Encode\EncodeConverterInterface;

#[Attribute]
final class DateTimeImmutableValueConverter implements DecodeConverterInterface, EncodeConverterInterface
{
    public function __construct(
        private string $encodeFormat = 'c',
    ) {}
    
    public function encodeTo(mixed $value): mixed
    {
        return $value->format($this->encodeFormat);
    }

    public function decodeTo(mixed $value): mixed
    {
        return new DateTimeImmutable($value);
    }
}
