<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\Encode\AllowEncode;
use Dschledermann\JsonCoder\Filter\Encode\EncodeFilterInterface;
use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;
use Dschledermann\JsonCoder\KeyConverter\PassThrough;
use Dschledermann\JsonCoder\ValueConverter\Encode\EncodeConverterInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * @template T
 */
final class Encoder
{
    use CoderTrait;

    private function __construct(
        private int $flags,
        /** @var EncodeUnit[] */
        private array $encodeUnits,
    ) {}

    /**
     * @template T
     * @param    class-string<T>         $targetClass
     * @param    int                     $flags
     * @param    ?KeyConverterInterface  $defaultKeyConverter
     * @param    ?EncodeFilterInterface  $defaultFilter
     *
     * @return   Encoder<T>
     */
    public static function create(
        string $targetClass,
        int $flags = 0,
        ?KeyConverterInterface $defaultKeyConverter = null,
        ?EncodeFilterInterface $defaultFilter = null,
    ): Encoder
    {
        $encodeUnits = [];
        $reflector = new ReflectionClass($targetClass);

        $keyConverter = self::getKeyConverter($reflector);
        $keyConverter = $keyConverter?? $defaultKeyConverter;
        $keyConverter = $keyConverter?? new PassThrough();

        $filter = self::getFilter($reflector);
        $filter = $filter?? $defaultFilter;
        $filter = $filter?? new AllowEncode();

        foreach ($reflector->getProperties() as $property) {

            $filterUse = self::getFilter($property) ?? $filter;

            $keyConverterUse = self::getKeyConverter($property) ?? $keyConverter;

            $name = $property->getName();
            $key = $keyConverterUse->getName($name);
            $type = $property->getType();

            if (in_array($type->getName(), ['bool', 'string', 'int', 'float'])) {
                $encodeUnits[] = EncodeUnit::simple(
                    $property,
                    $key,
                    $filterUse,
                    self::getValueConverter($property),
                );
            } elseif ($type->getName() == 'array') {
                if ($listType = self::getArrayListType($property, $reflector)) {
                    $encodeUnits[] = EncodeUnit::listType(
                        $property,
                        $key,
                        $filterUse,
                        $listType
                            ->withFlags($flags)
                            ->withKeyConverter($keyConverterUse)
                            ->withEncodeFilter($filterUse),
                    );
                } else {
                    $encodeUnits[] = EncodeUnit::simple($property, $key, $filterUse, null);
                }
            } elseif (class_exists($type->getName())) {
                $encodeUnits[] = EncodeUnit::subEncoder(
                    $property,
                    $key,
                    $filterUse,
                    Encoder::create(
                        $type->getName(),
                        $flags,
                        $keyConverterUse,
                        $filterUse,
                    ),
                );
            } else {
                throw new CoderException(sprintf(
                    "[ohneeNg9y] I don't know what to do with '%s'",
                    $type->getName(),
                ));
            }
        }

        return new Encoder($flags, $encodeUnits);
    }

    /**
     * Encode object T into JSON
     *
     * @param T     $obj      Object to encode
     *
     * @return string           JSON
     */
    public function encode(object $obj): string
    {
        return json_encode($this->realEncode($obj), $this->flags);
    }

    /**
     * Encode array of object T into JSON
     *
     * @param array<T>    $listOfObjs      Objects to encode
     *
     * @return string           JSON
     */
    public function encodeArray(array $listOfObjs): string
    {
        $arr = [];
        foreach ($listOfObjs as $obj) {
            $arr[] = $this->realEncode($obj);
        }
        return json_encode($arr, $this->flags);
    }

    /**
     * Encode object T into JSON
     *
     * @param T     $subject      Object to encode
     *
     * @return array        Converted value
     */
    private function realEncode(object $subject): array
    {
        $arr = [];

        foreach ($this->encodeUnits as $encodeUnit) {
            $reflection = $encodeUnit->reflection;
            $value = $reflection->getValue($subject);

            // Should we even encode?
            if (!$encodeUnit->filter->doEncode($reflection->getName(), $value)) {
                continue;
            }

            // Is the value null?
            if (is_null($value)) {
                $arr[$encodeUnit->keyName] = null;
                continue;
            }

            // Direct encoding?
            if ($encodeUnit->directEncode) {
                // Apply convertion
                if ($valueConverter = $encodeUnit->valueConverter) {
                    $arr[$encodeUnit->keyName] = $valueConverter->convert($value);
                } else {
                    $arr[$encodeUnit->keyName] = $value;
                }
                continue;
            }

            // A sub structure?
            if ($subEncoder = $encodeUnit->encoder) {
                $arr[$encodeUnit->keyName] = $subEncoder->realEncode($value);
                continue;
            }

            // A sub list?
            if ($listType = $encodeUnit->listType) {
                $subArr = [];
                if ($listType->isSimpleType()) {
                    if ($valueConverter = $encodeUnit->valueConverter) {
                        foreach ($value as $subValue) {
                            $subArr[] = $valueConverter->convert($subValue);
                        }
                    } else {
                        $subArr = $value;
                    }
                } elseif ($subEncoder = $listType->getEncoder()) {
                    foreach ($value as $subValue) {
                        $subArr[] = $subEncoder->realEncode($subValue);
                    }
                } else {
                    throw new CoderException(sprintf(
                        "[Haez3aeph] Configuration for %s is in an invalid state",
                        $encodeUnit->reflection->getName(),
                    ));
                }

                $arr[$encodeUnit->keyName] = $subArr;
                continue;
            }

            throw new CoderException(sprintf(
                "[Pho4eYoht] Unable to handle field %s",
                $reflection->getName(),
            ));
        }

        return $arr;
    }

    private static function getValueConverter(
        ReflectionProperty $reflect,
    ): ?EncodeConverterInterface
    {
        foreach ($reflect->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof EncodeConverterInterface) {
                return $instance;
            }
        }
        return null;
    }

    private static function getFilter(
        ReflectionClass|ReflectionProperty $reflect,
    ): ?EncodeFilterInterface
    {
        $attributes = $reflect->getAttributes();
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof EncodeFilterInterface) {
                return $instance;
            }
        }
        return null;
    }
}
