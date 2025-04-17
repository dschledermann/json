<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\KeyConverter\ToLower;
use Dschledermann\JsonCoder\KeyConverter\ToSnakeCase;
use Dschledermann\JsonCoder\KeyConverter\ToUpper;
use Dschledermann\JsonCoder\KeyConverter\Rename;
use Dschledermann\JsonCoder\KeyConverter\UpperCaseFirst;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    public function testUpperLower(): void
    {
        $toLower = new ToLower();
        $this->assertSame('wellhellothere', $toLower->getName('wellHelloThere'));

        $toUpper = new ToUpper();
        $this->assertSame('WELLHELLOTHERE', $toUpper->getName('wellHelloThere'));
    }

    public function testSnakeCase(): void
    {
        $caseConverter = new ToSnakeCase();
        $this->assertSame('well_hello_there', $caseConverter->getName('wellHelloThere'));
    }

    public function testUcFirst(): void
    {
        $caseConverter = new UpperCaseFirst();
        $this->assertSame("DavsDu", $caseConverter->getName("davsDu"));
    }

    public function testRename(): void
    {
        $rename = new Rename('drPjuskeBusk');
        $this->assertSame('drPjuskeBusk', $rename->getName('memememe'));
    }
}
