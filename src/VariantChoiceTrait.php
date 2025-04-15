<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use ReflectionClass;
use ReflectionObject;

trait VariantChoiceTrait
{
    /**
     * Returns the first variant that is set, if any apply.
     */
    public function getVariantType(): ?string
    {
        $reflector = new ReflectionObject($this);
        $properties = $reflector->getProperties();

        foreach ($properties as $property) {
            if (!is_null($property->getValue($this))) {
                return $property->getType()->getName();
            }
        }

        return null;
    }

    /**
     * Instantiate the choice object from a given element variant
     * If the type doesn't match, then null is returned.
     * This is a usefull shorthand for creating the payloads from a given variant.
     */
    public static function createFromVariant(object $element): ?static
    {
        $reflector = new ReflectionClass(get_called_class());
        $properties = $reflector->getProperties();

        foreach ($properties as $property) {
            if ($property->getType()->getName() === get_class($element)) {
                $instance = $reflector->newInstanceWithoutConstructor();
                $property->setValue($instance, $element);
                return $instance;
            }
        }

        return null;
    }
}
