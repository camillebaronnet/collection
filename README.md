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
