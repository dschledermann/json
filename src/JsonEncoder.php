<?php

declare(strict_types=1);

namespace Dschledermann\Json;

use Closure;
use ReflectionObject;

final class JsonEncoder
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

    /**
     * Do an JSON encoding of the object, respecting the key case converter given.
     * @param object $object
     */
    public function encode($object): string
    {
        return json_encode($this->realEncode($object));
    }

    public function encodeArray($arr): string
    {
        $result = [];
        foreach ($arr as $key => $obj) {
            $result[$key] = $this->realEncode($obj);
        }

        return json_encode($result);
    }

    private function realEncode($object): array
    {
        $reflector = new ReflectionObject($object);
        $properties = $reflector->getProperties();

        $result = [];
        $callBack = $this->keyCaseConverter;

        foreach ($properties as $property) {
            $name = $callBack($property->getName());
            $value = $property->getValue($object);

            if (is_object($value)) {
                $result[$name] = $this->realEncode($value);
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }
}
