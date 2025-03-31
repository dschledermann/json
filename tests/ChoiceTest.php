<?php

declare(strict_types=1);

namespace Tests\Dschledermann\JsonCoder;

use Dschledermann\JsonCoder\AbstractChoice;
use Dschledermann\JsonCoder\Coder;
use PHPUnit\Framework\TestCase;

final class Person
{
    public function __construct(
        public string $name,
    ) {}
}

final class Coordinate
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}

final class Car
{
    public function __construct(
        public string $brand,
        public float $horsePowers,
    ) {}
}

final class SomeThingUnreleated {
    public function __construct(
        public string $something,
    ) {}
}

final class Payload extends AbstractChoice
{
    public ?Person $person = null;
    public ?Coordinate $coordinate = null;
    public ?Car $car = null;
}

class ChoiceTest extends TestCase
{
    public function testDecodePayloadSimple(): void
    {
        $json = '{"person":{"name":"James Bond"}}';
        $decoder = new Coder();
        $payload = $decoder->decode($json, Payload::class);
        $this->assertEquals(Person::class, $payload->getVariantType());
        $this->assertEquals("James Bond", $payload->person->name);
        $this->assertNull($payload->coordinate);

        $json = '{"coordinate":{"x":0.3750001200618655,"y":-0.2166393884377127}}';
        $payload = $decoder->decode($json, Payload::class);
        $this->assertEquals(Coordinate::class, $payload->getVariantType());
        $this->assertEquals(0.3750001200618655, $payload->coordinate->x);
    }

    public function testCreateFromVariant(): void
    {
        $person = new Person("Dr Pjuskebusk");
        $payload = Payload::createFromVariant($person);
        $this->assertEquals($person, $payload->person);
        $this->assertNull($payload->coordinate);
        $this->assertNull($payload->car);

        $unrelated = new SomeThingUnreleated("Nah nah nah");
        $this->assertNull(Payload::createFromVariant($unrelated));
    }

    public function testMissingPayload(): void
    {
        $json = '{"unrelated":{"something":"else"}}';

        $decoder = new Coder();

        $payload = $decoder->decode($json, Payload::class);
        $this->assertNull($payload->getVariantType());
    }

    public function testArrayOfChoices(): void
    {
        $json = '[{"car":{"brand":"Volvo","horsePowers":193}},{"car":{"brand":"Tesla","horsePowers":320}},{"person":{"name":"Daniel"}}]';
        $decoder = new Coder();

        $listOfChoices = $decoder->decodeArray($json, Payload::class);

        $this->assertSame(3, count($listOfChoices));
        $this->assertEquals(Car::class, $listOfChoices[0]->getVariantType());
        $this->assertEquals("Volvo", $listOfChoices[0]->car->brand);
        $this->assertEquals(Car::class, $listOfChoices[1]->getVariantType());
        $this->assertEquals("Tesla", $listOfChoices[1]->car->brand);
        $this->assertEquals(Person::class, $listOfChoices[2]->getVariantType());
        $this->assertEquals("Daniel", $listOfChoices[2]->person->name);
    }
}
