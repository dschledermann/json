<?php

declare(strict_types=1);

namespace Dschledermann\JsonCoder;

use Closure;

interface CoderInterface
{
    /**
     * Use this callback to convert the keys
     * This is useful for converting to and from JSON objects where
     * the case is different from PHP's camelCase.
     */
    public function withKeyCaseConverter(Closure $converter): CoderInterface;

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
     */
    public function encodeArray(array $objects): string;

    /**
     * Attempt to decode JSON into a given class type
     */
    public function decode(string $src, string $className): object;

    /**
     * Attempt to decode JSON into an array of a given class type
     */
    public function decodeArray(string $src, string $className): array;
}

