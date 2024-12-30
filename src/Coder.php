<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\FilterInterface;
use Dschledermann\JsonCoder\Filter\KeepAllFields;
use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;
use Dschledermann\JsonCoder\KeyConverter\PassThrough;
use Dschledermann\JsonCoder\ValueConverter\Encode\EncoderInterface;
use Dschledermann\JsonCoder\ValueConverter\Decode\DecoderInterface;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

final class Coder implements CoderInterface
{
    private int $encodeFlags = 0;
    private int $decodeFlags = 0;

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

    public function encode(object $object): string
    {
        return json_encode(
            $this->realEncode($object, new PassThrough(), new KeepAllFields()),
            $this->encodeFlags,
        );
    }

    public function encodeArray(array $arr): string
    {
        return json_encode(
            $this->realEncodeArray($arr, new PassThrough(), new KeepAllFields()),
            $this->encodeFlags,
        );
    }

    private function realEncode(
        object $object,
        KeyConverterInterface $keyConverter,
        FilterInterface $filter,
    ): array {
        $reflector = new ReflectionObject($object);
        $properties = $reflector->getProperties();
        $keyConverter = $this->getKeyConverter($reflector) ?? $keyConverter;
        $filter = $this->getFilter($reflector) ?? $filter;

        $result = [];
        // Traverse every property defined on the class
        foreach ($properties as $property) {

            // Get any filters and conversions used
            $keyConverterUse = $this->getKeyConverter($property) ?? $keyConverter;
            $filterUse = $this->getFilter($property) ?? $filter;
            $name = $property->getName();
            $value = $property->getValue($object);

            // If we are to encode the property
            if ($filterUse->doEncodeField($name, $value)) {

                // Key what key we're supposed to use for that property
                $key = $keyConverterUse->getName($name);

                // If it's an object
                if (is_object($value)) {

                    // Recurse
                    $result[$key] = $this->realEncode(
                        $value,
                        $keyConverterUse,
                        $filterUse,
                    );
                } else if (is_array($value)) {
                    // arrays also recurse
                    $result[$key] = $this->realEncodeArray(
                        $value,
                        $keyConverterUse,
                        $filterUse,
                    );
                } else {

                    // Get the encoder, if any
                    $encoder = $this->getValueEncoder($property);

                    if ($encoder) {
                        $value = $encoder->encode($value);
                    }

                    // Other values are just added
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function realEncodeArray(
        array $arr,
        KeyConverterInterface $keyConverter,
        FilterInterface $filter,
    ): array {
        $result = [];
        foreach ($arr as $key => $element) {
            if (is_object($element)) {
                $result[$key] = $this->realEncode($element, $keyConverter, $filter);
            } else if (is_array($element)) {
                $result[$key] = $this->realEncodeArray($element, $keyConverter, $filter);
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
            new PassThrough(),
        );
    }

    public function decodeArray(string $str, string $className): array
    {
        $result = [];
        $src = json_decode($str, true, 512, $this->decodeFlags);
        $keyConvert = new PassThrough();

        foreach ($src as $key => $val) {
            $result[$key] = $this->realDecode($val, $className, $keyConvert);
        }

        return $result;
    }

    private function realDecode(
        array $src,
        string $className,
        KeyConverterInterface $keyConverter,
    ): object {
        $reflector = new ReflectionClass($className);
        $keyConverter = $this->getKeyConverter($reflector) ?? $keyConverter;
        $properties = $reflector->getProperties();
        $instance = $reflector->newInstanceWithoutConstructor();

        // We'll look at every property defined on the class
        foreach ($properties as $property) {

            // Determine what its key would look like in the JSON
            $keyConverterUse = $this->getKeyConverter($property) ?? $keyConverter;
            $name = $property->getName();
            $key = $keyConverterUse->getName($name);

            // If it exists
            if (array_key_exists($key, $src)) {
                // It does.. cool

                if (class_exists($property->getType()->getName())) {
                    // Check if it should be another class

                    // Recurse into that..
                    $property->setValue(
                        $instance,
                        $this->realDecode(
                            $src[$key],
                            $property->getType()->getName(),
                            $keyConverterUse,
                        ),
                    );
                } else {
                    // If it's not a class, decode and assign
                    $decoder = $this->getValueDecoder($property);
                    if ($decoder) {
                        $property->setValue($instance, $decoder->decode($src[$key]));
                    } else {
                        $property->setValue($instance, $src[$key]);
                    }
                }
            } else {
                // No?

                // Check if it's allows NULL
                if ($property->getType()->allowsNull()) {
                    // Pheww..
                    $property->setValue($instance, null);
                } else {
                    // The field should be there, but is not. Giving up.
                    throw new CoderException(sprintf(
                        '[eeg7phaeM] The field "%s" was missing',
                        $key,
                    ));
                }
            }
        }

        return $instance;
    }

    private function getKeyConverter(ReflectionClass|ReflectionProperty $reflector): ?KeyConverterInterface
    {
        $attributes = $reflector->getAttributes();
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof KeyConverterInterface) {
                return $instance;
            }
        }
        return null;
    }

    private function getValueEncoder(ReflectionProperty $reflect): ?EncoderInterface
    {
        foreach ($reflect->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof EncoderInterface) {
                return $instance;
            }
        }
        return null;
    }

    private function getValueDecoder(ReflectionProperty $reflect): ?DecoderInterface
    {
        foreach ($reflect->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof DecoderInterface) {
                return $instance;
            }
        }
        return null;
    }

    private function getFilter(ReflectionClass|ReflectionProperty $reflect): ?FilterInterface
    {
        $attributes = $reflect->getAttributes();
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof FilterInterface) {
                return $instance;
            }
        }
        return null;
    }
}
