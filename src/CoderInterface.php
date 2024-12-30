<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

interface CoderInterface
{
    /**
     * Pass these flags to the json_encode()-function.
     */
    public function withEncodeFlags(int $flags): CoderInterface;

    /**
     * Pass these flags to the json_decode()-function.
     */
    public function withDecodeFlags(int $flags): CoderInterface;

    /**
     * Encode object into JSON
     */
    public function encode(object $object): string;

    /**
     * Encode array of objects into JSON
     * @param array<object> $objects
     */
    public function encodeArray(array $objects): string;

    /**
     * Attempt to decode JSON into a given class type.
     * If successful it will return an object of the same type as given in the
     * className argument.
     */
    public function decode(string $src, string $className): ?object;

    /**
     * Attempt to decode JSON into an array of a given class type.
     * If successful it will return an array of objects of the same type as given
     * in the className argument.
     * @return array<object>
     */
    public function decodeArray(string $src, string $className): array;
}
