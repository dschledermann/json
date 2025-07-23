<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\Filter\Decode\AllowDecode;
use Dschledermann\JsonCoder\Filter\Decode\DecodeFilterInterface;
use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;
use Dschledermann\JsonCoder\KeyConverter\PassThrough;
use Dschledermann\JsonCoder\ValueConverter\Decode\DecodeConverterInterface;
use ReflectionClass;
use ReflectionException;
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

        try {
            $reflector = new ReflectionClass($targetClass);
        } catch (ReflectionException $e) {
            throw new CoderException(sprintf(
                "[Aet7ush7e] Error creating decoder: '%s'",
                $e->getMessage(),
            ));
        }

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

            if (is_null($type)) {
                throw new CoderException(sprintf(
                    "[iKe7Jue9s] Missing type for %s::%s",
                    $targetClass,
                    $name,
                ));
            }

            $decodeUnit = new DecodeUnit($property, $key);
            $decodeUnits[] = $decodeUnit;

            if ($valueConverter = self::getValueConverter($property)) {
                $decodeUnit->setValueConverter($valueConverter);
            }

            if (in_array($type->getName(), ['bool','string','int','float'])) {
                $decodeUnit->setDirectEncode(true);
            } elseif ($type->getName() == 'array') {
                // Recurse into a list
                $decodeUnit->setListType(
                    self::getArrayListType($property, $reflector)
                        ->setDecodeFilter($filterUse)
                        ->setKeyConverter($keyConverterUse),
                );
            } elseif (class_exists($type->getName())) {
                // Apply as a substructure
                $decodeUnit->setSubDecoder(
                    Decoder::create(
                        $type->getName(),
                        $flags,
                        $keyConverterUse,
                        $filterUse,
                    ),
                );
            } else {
                throw new CoderException(sprintf(
                    "[ieWohf4ba] I don't know what to do with '%s'. Does the type exist?",
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
        return $this->realDecode(json_decode($src, true, 512, $this->flags));
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

        foreach ($src as $val) {
            $result[] = $this->realDecode($val);
        }

        return $result;
    }

    /**
     * @param array $values  Decoded JSON
     *
     * @return T
     */
    public function realDecode(array $values): object
    {
        $instance = $this->reflector->newInstanceWithoutConstructor();

        foreach ($this->decodeUnits as $decodeBag) {
            $reflection = $decodeBag->reflection;

            if (array_key_exists($decodeBag->keyName, $values)) {

                // If we have a value decoder, then that rules
                if ($valueConverter = $decodeBag->valueConverter) {
                    if ($decodeBag->listType) {
                        $newValue = [];
                        foreach ($values[$decodeBag->keyName] as $k => $v) {
                            $newValue[$k] = $valueConverter->decodeTo($v);
                        }
                        $reflection->setValue($instance, $newValue);
                    } else {
                        $reflection->setValue(
                            $instance,
                            $valueConverter->decodeTo($values[$decodeBag->keyName]),
                        );
                    }
                    continue;
                }

                if ($decodeBag->directDecode) {
                    $reflection->setValue($instance, $values[$decodeBag->keyName]);
                    continue;
                }

                if ($subDecoder = $decodeBag->decoder) {
                    // This is an object of a defined type
                    $reflection->setValue(
                        $instance,
                        $subDecoder->realDecode($values[$decodeBag->keyName]),
                    );
                    continue;
                }

                if ($listType = $decodeBag->listType) {
                    // This is an array of sorts
                    $arrayValues = [];

                    if ($listType->isRawArray()) {
                        $arrayValues = $values[$decodeBag->keyName];
                    } elseif ($listType->isSimpleType()) {
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
                                ->realDecode($subValue);
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
