<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\Encode\EncodeFilterInterface;
use Dschledermann\JsonCoder\ValueConverter\Encode\EncodeConverterInterface;
use ReflectionProperty;

final class EncodeUnit
{
    public function __construct(
        public ReflectionProperty $reflection,
        public string $keyName,
        public EncodeFilterInterface $filter,
        public bool $directEncode = false,
        public ?EncodeConverterInterface $valueConverter = null,
        public ?ListType $listType = null,
        public ?Encoder $encoder = null,
    ) {}

    public function setDirectEncode(bool $directEncode): self
    {
        $this->directEncode = $directEncode;
        return $this;
    }

    public function setValueConverter(EncodeConverterInterface $valueConverter): self
    {
        $this->valueConverter = $valueConverter;
        return $this;
    }

    public function setListType(ListType $listType): self
    {
        $this->listType = $listType;
        return $this;
    }

    public function setSubEncoder(Encoder $encoder): self
    {
        $this->encoder = $encoder;
        return $this;
    }
}
