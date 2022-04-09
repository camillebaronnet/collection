<?php

declare(strict_types=1);

namespace Camillebaronnet\Collection;

use ArrayIterator;
use Camillebaronnet\Collection\Traits\HasFilterOperation;
use Camillebaronnet\Collection\Traits\HasReduceOperation;
use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate, Countable
{
    use HasReduceOperation;
    use HasFilterOperation;

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

    public function reduce(Closure $callback, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this as $currentKey => $currentValue) {
            $result = $callback($result, $currentValue, $currentKey);
        }

        return $result;
    }

    public function filter(Closure $callback): Collection
    {
        return new Collection((static function ($callback, iterable $iterator) {
            foreach ($iterator as $item) {
                if($callback($item)) {
                    yield $item;
                }
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

    public function each(Closure $callback): Collection
    {
        foreach($this->stream as $key => $element) {
            if($callback($element, $key) === false) {
                break;
            }
        }

        return $this;
    }

    public function flatten(): Collection
    {
        return new Collection((static function (iterable $iterator) {
            foreach ($iterator as $item) {
                foreach($item as $subItem) {
                    yield $subItem;
                }
            }
        })($this));
    }

    public function flatMap(Closure $callback): Collection
    {
       return $this->map($callback)->flatten();
    }
}
