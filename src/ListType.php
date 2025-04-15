<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Attribute;
use Dschledermann\JsonCoder\Filter\Decode\DecodeFilterInterface;
use Dschledermann\JsonCoder\Filter\Encode\EncodeFilterInterface;
use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;

#[Attribute]
final class ListType
{
    private bool $simpleType = true;
    private string $typeName = '';
    private string $classRef = '';
    private ?Decoder $decoder = null;
    private ?Encoder $encoder = null;
    private ?KeyConverterInterface $defaultKeyConverter = null;
    private ?EncodeFilterInterface $encodeFilter = null;
    private ?DecodeFilterInterface $decodeFilter = null;
    private int $flags = 0;

    public function __construct(string $typeName, string $namespace = '')
    {
        if (in_array(
            $typeName,
            [
                'bool',
                'boolean',
                'string',
                'int',
                'integer',
                'float',
                'double',
            ],
        )) {
            $this->simpleType = true;
            $this->typeName = match ($typeName) {
                'bool' => 'boolean',
                'int' => 'integer',
                'float' => 'double',
                default => $typeName,
            };
        } else {
            $this->simpleType = false;

            if (substr($typeName, 0, 1) == "\\" || !$namespace) {
                $this->classRef = $typeName;
            } else {
                $this->classRef = $namespace . "\\" . $typeName;
            }
        }
    }

    public static function createEncode(
        string $typeName,
        string $namespace,
        int $flags,
        KeyConverterInterface $keyConverter,
        EncodeFilterInterface $encodeFilter,
    ): ListType
    {
        $listType = new ListType($typeName, $namespace);
        $listType->flags = $flags;
        $listType->defaultKeyConverter = $keyConverter;
        $listType->encodeFilter = $encodeFilter;
        return $listType;
    }

    public function withFlags(int $flags): ListType
    {
        $clone = clone $this;
        $clone->flags = $flags;
        return $clone;
    }

    public function withKeyConverter(KeyConverterInterface $keyConverter): ListType
    {
        $clone = clone $this;
        $clone->defaultKeyConverter = $keyConverter;
        return $clone;
    }

    public function withEncodeFilter(EncodeFilterInterface $encodeFilter): ListType
    {
        $clone = clone $this;
        $clone->encodeFilter = $encodeFilter;
        return $clone;
    }

    public function withDecodeFilter(DecodeFilterInterface $decodeFilter): ListType
    {
        $clone = clone $this;
        $clone->decodeFilter = $decodeFilter;
        return $clone;
    }

    public function isSimpleType(): bool
    {
        return $this->simpleType;
    }

    public function getType(): string
    {
        return $this->typeName;
    }

    public function getDecoder(): Decoder
    {
        if (is_null($this->decoder)) {
            $this->decoder = Decoder::create(
                $this->classRef,
                $this->flags,
                $this->defaultKeyConverter,
                $this->decodeFilter,
            );
        }
        return $this->decoder;
    }

    public function getEncoder(): Encoder
    {
        if (is_null($this->encoder)) {
            $this->encoder = Encoder::create(
                $this->classRef,
                $this->flags,
                $this->defaultKeyConverter,
                $this->encodeFilter,
            );
        }
        return $this->encoder;
    }
}
