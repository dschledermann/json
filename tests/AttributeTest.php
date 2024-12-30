<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\KeyConverter\CaseConverter;
use Dschledermann\JsonCoder\KeyConverter\ToLower;
use Dschledermann\JsonCoder\KeyConverter\ToUpper;
use Dschledermann\JsonCoder\KeyConverter\Rename;
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

    public function testHappyCaseConverter(): void
    {
        $caseConverter = new CaseConverter('Snake');
        $this->assertSame('well_hello_there', $caseConverter->getName('wellHelloThere'));

        $caseConverter = new CaseConverter('Pascal');
        $this->assertSame('WellHelloThere', $caseConverter->getName('wellHelloThere'));
    }

    public function testUnknownCaseConverter(): void
    {
        $caseConverter = new CaseConverter('DrPjuskebusk');
        $this->expectExceptionMessage("[Thaqu4iet] Unknown format 'DrPjuskebusk'");
        $caseConverter->getName('wellHelloThere');
    }

    public function testRename(): void
    {
        $rename = new Rename('drPjuskeBusk');
        $this->assertSame('drPjuskeBusk', $rename->getName('memememe'));
    }
}
