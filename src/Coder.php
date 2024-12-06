<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Closure;
use ReflectionClass;
use ReflectionObject;

final class Coder implements CoderInterface
{
    private int $encodeFlags = 0;
    private int $decodeFlags = 0;
    private bool $skipNulls = true;
    private Closure $converter;

    public function __construct()
    {
        $this->converter = fn ($s) => $s;
    }

    public function withKeyCaseConverter(Closure $converter): CoderInterface
    {
        $clone = clone $this;
        $clone->converter = $converter;
        return $clone;
    }

    public function withDecodeFlags(int $flags): CoderInterface
    {
        $clone = clone $this;
        $clone->decodeFlags = $flags;
        return $clone;
    }

    public function withEncodeFlags(int $flags): CoderInterface
    {
        $clone = clone $this;
        $clone->encodeFlags = $flags;
        return $clone;
    }

    public function withEncodeNull(): CoderInterface
    {
        $clone = clone $this;
        $clone->skipNulls = false;
        return $clone;
    }

    public function withSkipEncodeNull(): CoderInterface
    {
        $clone = clone $this;
        $clone->skipNulls = true;
        return $clone;
    }

    public function encode(object $object): string
    {
        return json_encode($this->realEncode($object), $this->encodeFlags);
    }

    public function encodeArray(array $arr): string
    {
        return json_encode($this->realEncodeArray($arr), $this->encodeFlags);
    }

    private function realEncode(object $object): array
    {
        $reflector = new ReflectionObject($object);
        $properties = $reflector->getProperties();

        $result = [];
        $callBack = $this->converter;

        foreach ($properties as $property) {
            $name = $callBack($property->getName());
            $value = $property->getValue($object);

            if (is_object($value)) {
                $result[$name] = $this->realEncode($value);
            } else if (is_array($value)) {
                $result[$name] = $this->realEncodeArray($value);
            } else if (is_null($value) && $this->skipNulls) {
                // Skipping nulls
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    private function realEncodeArray(array $arr): array
    {
        $result = [];
        foreach ($arr as $key => $element) {
            if (is_object($element)) {
                $result[$key] = $this->realEncode($element);
            } else if (is_array($element)) {
                $result[$key] = $this->realEncodeArray($element);
            } else {
                $result[$key] = $element;
            }
        }
        return $result;
    }

    public function decode(string $src, string $className): object
    {
        return $this->realDecode(
            json_decode($src, true, 512, $this->decodeFlags),
            $className,
        );
    }

    public function decodeArray(string $str, string $className): array
    {
        $result = [];
        $src = json_decode($str, true, 512, $this->decodeFlags);

        foreach ($src as $key => $val) {
            $result[$key] = $this->realDecode($val, $className);
        }

        return $result;
    }

    private function realDecode(array $src, string $className): object
    {
        $reflector = new ReflectionClass($className);
        $properties = $reflector->getProperties();
        $instance = $reflector->newInstanceWithoutConstructor();
        $callBack = $this->converter;
        foreach ($properties as $property) {
            $key = $callBack($property->getName());

            if (array_key_exists($key, $src)) {
                if (class_exists($property->getType()->getName())) {
                    $property->setValue(
                        $instance,
                        $this->realDecode($src[$key], $property->getType()->getName()),
                    );
                } else {
                    $property->setValue($instance, $src[$key]);
                }
            } else {
                if ($property->getType()->allowsNull()) {
                    $property->setValue($instance, null);
                } else {
                    throw new CoderException(sprintf(
                        '[eeg7phaeM] The field "%s" was missing',
                        $key,
                    ));
                }
            }
        }

        return $instance;
    }
}
