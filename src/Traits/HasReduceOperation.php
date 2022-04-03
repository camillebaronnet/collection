<?php

namespace Camillebaronnet\Collection\Traits;

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
}