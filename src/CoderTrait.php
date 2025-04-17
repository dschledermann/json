<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\KeyConverter\KeyConverterInterface;
use ReflectionClass;
use ReflectionProperty;

trait CoderTrait
{
    /**
     * Discover the first attribute implementing KeyConverterInterface
     * on a reflection class or property.
     */
    protected static function getKeyConverter(ReflectionClass|ReflectionProperty $reflector): ?KeyConverterInterface
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

    /**
     * @template T
     * Discover or instantiate a ListType for an array list.
     * This can either be explicitly via an attribute, or guessed from the docblock
     * before the array declation.
     *
     * @param  ReflectionProperty   $property         Property to be examined.
     * @param  ReflectionClass<T>   $reflectionClass  The class where the property is
     *                                                attached to.
     *
     * @return ListType
     */
    protected static function getArrayListType(
        ReflectionProperty $property,
        ReflectionClass $reflectionClass,
    ): ListType
    {
        // Look for a ListType attribute
        $attribute = $property->getAttributes(ListType::class);

        // If we have this, then we have a solid match
        if (array_key_exists(0, $attribute)) {
            return $attribute[0]->newInstance();
        }

        // Is there a docblock we can create it from?
        $docblock = $property->getDocComment();
        if (!$docblock) {
            throw new CoderException(sprintf(
                "[ieyah4Ahp] No explicit ListType defined and no docblock for %s::%s",
                $reflectionClass->getName(),
                $property->getName(),
            ));
        }

        // Search for the type declarations in supported formats
        $namespace = $reflectionClass->getNamespaceName();
        if (preg_match('/@var (.+)\[\]/', $docblock, $matches)) {
            return new ListType($matches[1], $namespace);
        }

        if (preg_match('/@var array<(.+)>/', $docblock, $matches)) {
            return new ListType($matches[1], $namespace);
        }

        // Guess we can't do it..
        throw new CoderException(sprintf(
            "[xuequ9Fee] Unable to read the docblock on %s::%s",
            $reflectionClass->getName(),
            $property->getName(),
        ));
    }
}
