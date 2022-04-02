<?php

declare(strict_types=1);

namespace Camillebaronnet\Collection;

use ArrayIterator;
use Closure;
use Traversable;

final class Collection implements \IteratorAggregate
{
    private array|Traversable $stream;

    public function __construct(...$elements)
    {
        if(count($elements) === 1 && is_iterable($elements[0])) {
            $this->stream = $elements[0];
        } else {
            $this->stream = $elements;
        }
    }

    public function map(Closure $callback): Collection
    {
        return new Collection((static function ($callback, iterable $iterator) {
            foreach ($iterator as $item) {
                yield $callback($item);
            }
        })($callback, $this));
    }

    public function get(): Collection
    {
        return new Collection($this->stream instanceof Traversable
            ? $this->stream = iterator_to_array($this->stream)
            : $this->stream
        );
    }

    public function toArray(): array
    {
        return $this->get()->stream;
    }

    public function getIterator(): Traversable
    {
        if(is_array($this->stream)) {
            return new ArrayIterator($this->stream);
        }

        return $this->stream;
    }
}
