<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\ValueConverter\Decode\DecodeConverterInterface;
use ReflectionProperty;

final class DecodeUnit
{
    private function __construct(
        public ReflectionProperty $reflection,
        public bool $directDecode,
        public string $keyName,
        public ?DecodeConverterInterface $valueConverter,
        public ?ListType $listType = null,
        public ?Decoder $decoder = null,
    ) {}

    public static function simple(
        ReflectionProperty $reflection,
        string $keyName,
        ?DecodeConverterInterface $valueConverter,
    ): DecodeUnit
    {
        return new DecodeUnit(
            $reflection,
            true,
            $keyName,
            $valueConverter,
            null,
            null,
        );
    }

    public static function listType(
        ReflectionProperty $reflection,
        string $keyName,
        ListType $listType,
    ): DecodeUnit
    {
        return new DecodeUnit(
            $reflection,
            false,
            $keyName,
            null,
            $listType,
            null,
        );
    }

    public static function subDecoder(
        ReflectionProperty $reflection,
        string $keyName,
        Decoder $decoder,
    ): DecodeUnit
    {
        return new DecodeUnit(
            $reflection,
            false,
            $keyName,
            null,
            null,
            $decoder,
        );
    }
}
