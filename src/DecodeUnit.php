<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\ValueConverter\Decode\DecodeConverterInterface;
use ReflectionProperty;

final class DecodeUnit
{
    public function __construct(
        public ReflectionProperty $reflection,
        public string $keyName,
        public bool $directDecode = false,
        public ?DecodeConverterInterface $valueConverter = null,
        public ?ListType $listType = null,
        public ?Decoder $decoder = null,
    ) {}

    public function setDirectEncode(bool $directEncode): self
    {
        $this->directDecode = $directEncode;
        return $this;
    }

    public function setValueConverter(DecodeConverterInterface $valueConverter): self
    {
        $this->valueConverter = $valueConverter;
        return $this;
    }

    public function setListType(ListType $listType): self
    {
        $this->listType = $listType;
        return $this;
    }

    public function setSubDecoder(Decoder $decoder): self
    {
        $this->decoder = $decoder;
        return $this;
    }
}
