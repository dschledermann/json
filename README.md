

# JSON Coder

This package can encode and decode JSON to and from PHP classes.
It is useful for packing DTOs on queues, for cloud storage or just for HTTP-replies.
Reflection is used, and any constructor logic is surpassed.
The decoder is quite happy to ignore extra fields that may be in a payload,
so you can grab just the field your application uses.

## Installation

```bash
composer require dschledermann/json-coder
```

## Usage

Instantiate the Coder and configure it if you need to.

### Encoding
This is very straight forward:

Example:

```php
class SomeClass
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

$someObj = new SomeClass(
    "John Doe",
    45,
);

$coder = new Coder();
echo $coder->encode($someObj);
```

This will output:

```json
{"name":"John Doe","age":45}
```

### Decoding
Here you need to specify what class you wish to decode into.
Apart from this, it is basically the reverse:

```php
class SomeClass
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

$coder = new Coder();
$json = '{"name":"John Doe","age":45}';
print_r($coder->decode($json, SomeClass::class));
```

This will output something like:

```
SomeClass Object
(
    [name] => John Doe
    [age] => 45
)
```


### Configure the Coder

If you have some more advanced needs,
then it's possible to pass options to the ```json_encode()``` and ```json_decode()``` functions.

```php
$coder = (new Coder())->withEncodeFlags(JSON_PRETTY_PRINT);
```

If need to change the style of the keys,
then that's also possible.
This is useful when interfacing with API's or languages where the naming convention differs from PHP's.

```php
use Dschledermann\JsonCoder\KeyConverter\ToLower;

#[ToLower]
class SomeObj
{
    public function __construct(
        public string $myString,
        public int $myFancyInt,
    ) {}
}

$obj = new SomeObj("Walter White", 52);
$coder = (new Coder())->withEncodeFlags(JSON_PRETTY_PRINT);
echo $coder->encode($obj);
```

Outputs:
```json
{
    "mystring": "Walter White",
    "myfancyint": 52
}
```

Using jawira/case-converter wrapper to snake case:

```php

use Dschledermann\JsonCoder\KeyConverter\CaseConverter;

#[CaseConverter("Snake")]
class SomeObj
{
    public function __construct(
        public string $myString,
        public int $myFancyInt,
    ) {}
}

$obj = new SomeObj("Walter White", 52);
$coder = new Coder();
echo $coder->encode($obj);
```

Outputs:

```json
{"my_string":"Walter White","my_fancy_int":52}
```

The same "direction" of the key case converter is used both for encoding and decoding JSON,
so you don't have to configure the Coder in a different way depending on use.

See the test suite for more examples.


## Using the "Choice"

In many cases you can expect multiple variant on an API reply or AMQP queue.
Fortunately this packages also gives a convenient way to handle that.
This package has the AbstractChoice that will make it easier for you to define an
umbrella for all the types you expected to receive.

The assumption is that you only have one toplevel key,
and that key defines what variant you are dealing with.

There are some limitations on this:

1. Each key has to be of a unique type.
2. You should not have any additional properties on the payload choice class.
3. Check for null output. Decoding and encoding is done with soft reflection "magic".
   If there are no matches, you will receive a null value.

Consider that you have these two choices:

A person:
```php
final class Person
{
    public function __construct(
        public string $name,
    ) {}
}
```

Or a car:

```php
final class Car
{
    public function __construct(
        public string $brand,
        public float $horsePowers,
    ) {}
}
```

You can now create a payload class that has each as a variant:

```php
use Dschledermann\JsonCoder\AbstractChoice;
use Dschledermann\JsonCoder\Filter\SkipNull;

#[SkipNull]
final class Payload extends AbstractChoice
{
    public ?Person $person = null;
    public ?Car $car = null;
}
```

Consider this payload:

```json
[
  {"car":{"brand":"Volvo","horsePowers":193}},
  {"car":{"brand":"Tesla","horsePowers":320}},
  {"person":{"name":"Daniel"}}
]
```

This PHP-code will decode it:

```php
$decoder = new Coder();
$listOfChoices = $decoder->decodeArray($json, Payload::class);
print_r($listOfChoices);
```

Will output something like this:

```
Array
(
    [0] => Payload Object
        (
            [person] =>
            [car] => Car Object
                (
                    [brand] => Volvo
                    [horsePowers] => 193
                )

        )

    [1] => Payload Object
        (
            [person] =>
            [car] => Car Object
                (
                    [brand] => Tesla
                    [horsePowers] => 320
                )

        )

    [2] => Payload Object
        (
            [person] => Person Object
                (
                    [name] => Daniel
                )

            [car] =>
        )

)

```

The variable $listOfChoices will now contain Payload objects.
Each of them can be queried what variant they using the AbstractChoice::getVariantType() method.

### Encoding a choice

It's also useful to be able to wrap a variant object in the choice container.
Consider this code:

```php
$person = new Person("Mr. Bean");
$payload = Payload::createFromVariant($person);
print_r($payload);
```

This will output something like:

```
Payload Object
(
    [person] => Person Object
        (
            [name] => Mr. Bean
        )

    [car] =>
)
```

And if you encode it, the result will be this:

```json
{"person":{"name":"Mr. Bean"}}
```

### Decoding nested structures

Nested arrays present a challenge in PHP as the array declaration does not contain limitations on the types in the array.
There are a couple of conventions to work around this.
The most commonly used is a docblock comment.
The two formats supported are "T[]" and "array<T>".
In many cases this is sufficient.

```php
final class SomeType
{
    /** @var int[] */
    public array $listOfInts;
}
```

We can decode this JSON, and it will enforce that all the elements are indeed integers.

```json
{"listOfInts":[12,12,12,3,3,4]}
```

The code will throw an exception if an element in the list is not an int.

We can also coerce a substructure into a complex type:

```php
final class SomeType
{
    public string $someField;
	/** @var SubType[] */
	public array $subTypeList;
}

final class SubType
{
    public int $someValue;
}
```

This JSON can be decoded, and the elements in $subTypeList will all be of class "SubType".

```json
{"someField":"This is a string","subTypeList":[{"someValue":1},{"someValue":12},{"someValue":123}]}
```

Into something like this:

```
SomeType Object
  (
      [someField] => This is a string
      [subTypeList] => Array
          (
              [0] => SubType Object
                  (
                      [someValue] => 1
                  )

              [1] => SubType Object
                  (
                      [someValue] => 12
                  )

              [2] => SubType Object
                  (
                      [someValue] => 123
                  )

          )

  )
```

This works for simple, native types and for types within the same namespace.
If you are using types from differing namespaces, you have two options:

- Use the full path of the class in the type hinting
- Use the ListType as an attribute to indicate.

```php
namespace Path\To\SomeSpace;

final class SubType
{
    public string $value;
}
```

Using attribute:

```php
namespace Path\To\AnotherSpace;

use Dschledermann\JsonCoder\ListType;
use Path\To\SomeSpace\SubType;

final class SomeType
{
    #[ListType(SubType::class)]
    public array $list;
}
```

Using full path type hinting:

```php
namespace Path\To\AnotherSpace;

final class SomeType
{
    /** @var \Path\To\SomeSpace\SubType[] */
    public array $list;
}
```
