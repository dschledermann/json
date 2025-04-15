<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\Decode\AllowDecode;
use Dschledermann\JsonCoder\Filter\Decode\DecodeFilterInterface;
use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;
use Dschledermann\JsonCoder\KeyConverter\PassThrough;
use Dschledermann\JsonCoder\ValueConverter\Decode\DecodeConverterInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * @template T
 */
final class Decoder
{
    use CoderTrait;

    private function __construct(
        /** @var ReflectionClass<T> */
        private ReflectionClass $reflector,
        private int $flags,
        /** @var DecodeUnit[] */
        private array $decodeUnits,
    ) {}

    /**
     * @template T
     * @param class-string<T>          $targetClass
     * @param int                      $flags
     * @param ?KeyConverterInterface   $defaultKeyConverter
     * @param ?DecodeFilterInterface   $defaultFilter
     *
     * @return Decoder<T>
     */
    public static function create(
        string $targetClass,
        int $flags = 0,
        ?KeyConverterInterface $defaultKeyConverter = null,
        ?DecodeFilterInterface $defaultFilter = null,
    ): Decoder
    {
        $decodeUnits = [];
        $reflector = new ReflectionClass($targetClass);

        $keyConverter = self::getKeyConverter($reflector);
        $keyConverter = $keyConverter?? $defaultKeyConverter;
        $keyConverter = $keyConverter?? new PassThrough();

        $filter = self::getFilter($reflector);
        $filter = $filter?? $defaultFilter;
        $filter = $filter?? new AllowDecode();

        foreach ($reflector->getProperties() as $property) {

            // Should we even decode this field?
            $filterUse = self::getFilter($property) ?? $filter;

            if (!$filterUse->doDecode($property->getName())) {
                continue;
            }

            // Find out what names are used
            $keyConverterUse = self::getKeyConverter($property) ?? $keyConverter;

            $name = $property->getName();
            $key = $keyConverterUse->getName($name);
            $type = $property->getType();

            if (in_array($type->getName(), ['bool','string','int','float'])) {
                $decodeUnits[] = DecodeUnit::simple(
                    $property,
                    $key,
                    self::getValueConverter($property),
                );
            } elseif ($type->getName() == 'array') {
                // Recurse into a list
                if ($listType = self::getArrayListType($property, $reflector)) {
                    $decodeUnits[] = DecodeUnit::listType(
                        $property,
                        $key,
                        $listType
                            ->withFlags($flags)
                            ->withKeyConverter($keyConverterUse),
                    );
                } else {
                    $decodeUnits[] = DecodeUnit::simple($property, $key);
                }
            } elseif (class_exists($type->getName())) {
                // Apply as a substructure
                $decodeUnits[] = DecodeUnit::subDecoder(
                    $property,
                    $key,
                    Decoder::create(
                        $type->getName(),
                        $flags,
                        $keyConverterUse,
                        $filterUse,
                    ),
                );
            } else {
                throw new CoderException(sprintf(
                    "[ieWohf4ba] I don't know what to do with '%s'",
                    $type->getName(),
                ));
            }
        }

        return new Decoder($reflector, $flags, $decodeUnits);
    }

    /**
     * Decode JSON into an object of specific type.
     *
     * @param string $src        JSON string
     *
     * @return T                 Object of type T
     */
    public function decode(string $src): object
    {
        return $this->decipher(json_decode($src, true, 512, $this->flags));
    }

    /**
     * Decode JSON into an array of objects of specific type.
     *
     * @param string $src        JSON string
     *
     * @return array<T>          Array of objects of type defined in $classname
     */
    public function decodeArray(string $str): array
    {
        $result = [];
        $src = json_decode($str, true, 512, $this->flags);

        foreach ($src as $key => $val) {
            $result[$key] = $this->decipher($val);
        }

        return $result;
    }

    /**
     * @param array $values  Decoded JSON
     *
     * @return T
     */
    public function decipher(array $values): object
    {
        $instance = $this->reflector->newInstanceWithoutConstructor();

        foreach ($this->decodeUnits as $decodeBag) {
            $reflection = $decodeBag->reflection;

            if (array_key_exists($decodeBag->keyName, $values)) {

                if ($decodeBag->directDecode) {
                    // This is a simple value

                    if ($valueConverter = $decodeBag->valueConverter) {
                        $reflection->setValue(
                            $instance,
                            $valueConverter->convert($values[$decodeBag->keyName]));
                    } else {
                        $reflection->setValue($instance, $values[$decodeBag->keyName]);
                    }
                    continue;
                }

                if ($subDecoder = $decodeBag->decoder) {
                    // This is an object of a defined type

                    $reflection->setValue(
                        $instance,
                        $subDecoder->decipher($values[$decodeBag->keyName]),
                    );
                    continue;
                }

                if ($listType = $decodeBag->listType) {
                    // This is an array of sorts
                    $arrayValues = [];

                    if ($listType->isSimpleType()) {
                        // Elements are simple values
                        foreach ($values[$decodeBag->keyName] as $subValue) {
                            if (gettype($subValue) == $listType->getType()) {
                                $arrayValues[] = $subValue;
                            } else {
                                throw new CoderException(sprintf(
                                    "[Jae9ac9ai] Type mismatch got %s, expected %s",
                                    gettype($subValue),
                                    $listType->getType(),
                                ));
                            }
                        }
                    } elseif ($subDecoder = $listType->getDecoder()) {
                        foreach ($values[$decodeBag->keyName] as $subValue) {
                            $arrayValues[] = $listType
                                ->getDecoder()
                                ->decipher($subValue);
                        }
                    } else {
                        throw new CoderException('[Chajaip9e] Unable to decode list');
                    }

                    $reflection->setValue($instance, $arrayValues);

                    continue;
                }

                throw new CoderException(sprintf(
                    "[UjePuNa74] Unable to decode for property %s",
                    $reflection->getName(),
                ));
            } else {
                if ($reflection->getType()->allowsNull()) {
                    $reflection->setValue($instance, null);
                } else {
                    throw new CoderException(sprintf(
                        "[Aeghai9ja] Missing required field: %s / %s",
                        $decodeBag->keyName,
                        $reflection->getName(),
                    ));
                }
            }
        }

        return $instance;
    }

    private static function getValueConverter(
        ReflectionProperty $reflect,
    ): ?DecodeConverterInterface
    {
        foreach ($reflect->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof DecodeConverterInterface) {
                return $instance;
            }
        }
        return null;
    }

    protected static function getFilter(
        ReflectionClass|ReflectionProperty $reflect,
    ): ?DecodeFilterInterface
    {
        $attributes = $reflect->getAttributes();
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof DecodeFilterInterface) {
                return $instance;
            }
        }
        return null;
    }
}
