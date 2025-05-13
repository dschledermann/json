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
    private bool $rawArray = false;
    private bool $simpleType = true;
    private string $typeName = '';
    private string $classRef = '';
    private ?Decoder $decoder = null;
    private ?Encoder $encoder = null;
    private ?KeyConverterInterface $keyConverter = null;
    private ?EncodeFilterInterface $encodeFilter = null;
    private ?DecodeFilterInterface $decodeFilter = null;

    public function __construct(string $typeName, string $namespace = '')
    {
        if ($typeName === 'raw-array') {
            $this->rawArray = true;
        } else if (in_array(
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

    public function setKeyConverter(KeyConverterInterface $keyConverter): ListType
    {
        $this->keyConverter = $keyConverter;
        return $this;
    }

    public function setEncodeFilter(EncodeFilterInterface $encodeFilter): ListType
    {
        $this->encodeFilter = $encodeFilter;
        return $this;
    }

    public function setDecodeFilter(DecodeFilterInterface $decodeFilter): ListType
    {
        $this->decodeFilter = $decodeFilter;
        return $this;
    }

    public function isRawArray(): bool
    {
        return $this->rawArray;
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
                0,
                $this->keyConverter,
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
                0,
                $this->keyConverter,
                $this->encodeFilter,
            );
        }
        return $this->encoder;
    }
}
