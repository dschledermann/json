<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\Encode\EncodeFilterInterface;
use Dschledermann\JsonCoder\ValueConverter\Encode\EncodeConverterInterface;
use ReflectionProperty;

final class EncodeUnit
{
    private function __construct(
        public ReflectionProperty $reflection,
        public bool $directEncode,
        public string $keyName,
        public EncodeFilterInterface $filter,
        public ?EncodeConverterInterface $valueConverter,
        public ?ListType $listType,
        public ?Encoder $encoder,
    ) {}

    public static function simple(
        ReflectionProperty $reflection,
        string $keyName,
        EncodeFilterInterface $filter,
        ?EncodeConverterInterface $valueConverter,
    ): EncodeUnit
    {
        return new EncodeUnit(
            $reflection,
            true,
            $keyName,
            $filter,
            $valueConverter,
            null,
            null,
            null,
        );
    }

    public static function listType(
        ReflectionProperty $reflection,
        string $keyName,
        EncodeFilterInterface $filter,
        ListType $listType,
    ): EncodeUnit
    {
        return new EncodeUnit(
            $reflection,
            false,
            $keyName,
            $filter,
            null,
            $listType,
            null,
        );
    }

    public static function subEncoder(
        ReflectionProperty $reflection,
        string $keyName,
        EncodeFilterInterface $filter,
        Encoder $encoder,
    ): EncodeUnit
    {
        return new EncodeUnit(
            $reflection,
            false,
            $keyName,
            $filter,
            null,
            null,
            $encoder,
        );
    }
}
