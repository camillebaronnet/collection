<?php

namespace Camillebaronnet\Collection\Traits;

use Camillebaronnet\Collection\Collection;
use Closure;
use Generator;

trait HasFilterOperation
{
    abstract public function filter(Closure $callback): Collection;

    public function unique(): Collection
    {
        $uniqueElements = [];

        return $this->filter(function($search) use (&$uniqueElements) {
            $arrayIndex = array_search($search, $uniqueElements, true);
            if (false === $arrayIndex) {
                $uniqueElements[] = $search;
                return true;
            }

            return false;
        });
    }
}
