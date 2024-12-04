<?php

declare(strict_types=1);

namespace Dschledermann\Json;

use Closure;
use ReflectionClass;

final class JsonDecoder
{
    private Closure $keyCaseConverter;

    public function __construct(?Closure $keyCaseConverter = null)
    {
        if (!$keyCaseConverter) {
            $this->keyCaseConverter = function(string $from) {
                return $from;
            };
        } else {
            $this->keyCaseConverter = $keyCaseConverter;
        }
    }

    public function decode(string $str, string $className): object
    {
        return $this->realDecode(json_decode($str, true), $className);
    }


    public function decodeArray(string $str, string $className): array
    {
        $result = [];
        $src = json_decode($str, true);

        foreach ($src as $key => $val) {
            $result[$key] = $this->realDecode($val, $className);
        }

        return $result;
    }

    public function realDecode(array $src, string $className): object
    {
        $reflector = new ReflectionClass($className);
        $properties = $reflector->getProperties();
        $instance = $reflector->newInstanceWithoutConstructor();
        $callBack = $this->keyCaseConverter;
        foreach ($properties as $property) {
            $key = $callBack($property->getName());
            if (class_exists($property->getType()->getName())) {
                $property->setValue(
                    $instance,
                    $this->realDecode($src[$key], $property->getType()->getName())
                );
            } else {
                $property->setValue($instance, $src[$key]);
            }
        }

        return $instance;
    }
}
