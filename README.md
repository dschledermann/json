

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
class SomeObj
{
    public function __construct(
        public string $myString,
        public int $myFancyInt,
    ) {}
}

$obj = new SomeObj("Walter White", 52);
```

Some examples:

With arrow function for strtolower and pretty print:
```php
$coder = (new Coder())
            ->withKeyCaseConverter(fn ($s) => strtolower($s))
            ->withEncodeFlags(JSON_PRETTY_PRINT);
echo $coder->encode($obj);
```

Outputs:
```json
{
    "mystring": "Walter White",
    "myfancyint": 52
}
```

Using the
use jawira/case-converter to snake case:

```php
$coder = $coder = (new Coder())->withKeyCaseConverter(function(string $s): string {
    return (new Convert($s))->toSnake();
});
echo $coder->encode($obj);
```

Outputs:

```json
{"my_string":"Walter White","my_fancy_int":52}
```

The same "direction" of the key case converter is used both for encoding and decoding JSON,
so you don't have to configure the Coder in a different way depending on use.


```php
$str = '{"my.string":"Skyler White","my.fancy.int":40}';

$coder = (new Coder())->withKeyCaseConverter(function(string $s): string {
    return (new Convert($s))->toDot();
});

print_r($coder->decode($str, SomeObj::class));
```

This will output something like:

```
SomeObj Object
(
    [myString] => Skyler White
    [myFancyInt] => 40
)
```

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
