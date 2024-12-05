<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use ReflectionObject;

abstract class AbstractChoice
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
}
