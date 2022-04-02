# Collection

Collection provides a powerful data manipulation system based on the map principle.
Thanks to the php generators and the immutability, it allows to calculate the data as late as possible while preserving the memory.

## Installation

```sh
composer require camillebaronnet/collection
```

## How to use

```php
public Collection::__construct(...$elements)
public Collection::__construct(\Traversable|array $elements)
```
Exemple :

```php
$collection = (new Collection('foo', 'bar'))
    ->map(fn($word) => 'hello '.$word)
    ->map(fn($word) => strtoupper($word))
;

// The data is computed only here
var_dump($collection->toArray()); 
```

```
array(2) {
  [0]=>
  string(9) "HELLO FOO"
  [1]=>
  string(9) "HELLO BAR"
}
```
## Basic operations

- map
- filter
- reduce

### Map

The `map()` function iterates over each element and applies the transformation function provided by the user.

```php
map(fn($element) => /* ... */);
```

Example :

```php
(new Collection(10, 50, 100))
    ->map(fn($element) => $element * 2)
    ->toArray()
;

// [20, 100, 200]
```

### Filter

The `filter()` method test elements using that pass the test implemented by the provided function.

```php
filter(fn ($element) => /* ... */);
```

Example : 

```php
(new Collection(10, 50, 100))
    ->filter(fn($element) => $element > 20)
    ->toArray()
;
// [50, 100]
```

### Reduce

The `reduce()` method executes a reduction function provided by the user on each element, the result of each iteration is passed to the next iteration.

```php
reduce(fn($carry, $currentValue, $currentKey) => /* ... */, $initial);
```

Example :

```php

(new Collection(10, 50, 100))
    ->reduce(fn($carry, $current) => $carry + $current)
;
// 160
```
