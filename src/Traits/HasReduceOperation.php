<?php

namespace Camillebaronnet\Collection\Traits;

use Camillebaronnet\Collection\Collection;
use Camillebaronnet\Collection\CollectionGroup;
use Closure;

/**
 * @internal
 */
trait HasReduceOperation
{
    abstract public function reduce(Closure $callback, mixed $initial = null): mixed;

    public function sum(): int|float
    {
       return $this->reduce(fn($a, $b) => $a + $b, 0);
    }

    public function avg(): int|float
    {
        $nbItems = 0;
        return $this->reduce(function ($a, $b) use (&$nbItems) {
            $nbItems++;
            return $a + $b;
        }, 0) / $nbItems;
    }

    public function groupBy(Closure $callback): Collection
    {
        return new Collection((function() use ($callback) {
            foreach ($this->get()->map($callback)->unique() as $item) {
                yield new CollectionGroup(
                    key: $item,
                    elements: $this->filter(fn ($x) => $item === $callback($x)),
                );
            }
        })());
    }

    public function count(): int
    {
        return $this->reduce(function ($nbItems) {
            $nbItems++;
            return $nbItems;
        }, 0);
    }
}