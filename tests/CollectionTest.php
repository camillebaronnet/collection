<?php

declare(strict_types=1);

use Camillebaronnet\Collection\Collection;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function test_should_transform_using_map(): void
    {
        $list = new Collection('foo', 'bar');
        self::assertEquals(
            new Collection('FOO', 'BAR'),
            $list->map(fn($word) => strtoupper($word))->get()
        );
    }

    public function test_map_should_be_immutable(): void
    {
        $originalList = new Collection('foo', 'bar');
        $mappedList = $originalList->map(fn($word) => strtoupper($word));

        self::assertEquals(new Collection('foo', 'bar'), $originalList->get());
        self::assertEquals(new Collection('FOO', 'BAR'), $mappedList->get());
    }

    public function test_map_should_be_stream(): void
    {
        $trace = [];

        // Allow follow execution steps
        $spy = static function ($result, $log) use (&$trace) {
            $trace[] = $log;
            return $result;
        };

        $originalList = new Collection('foo', 'bar');
        $mappedList = $originalList
            ->map(fn($word) => $spy(strtoupper($word), 'upper'))
            ->map(fn($word) => $spy(strtolower($word), 'lower'));

        self::assertSame([], $trace);

        $mappedList->get();

        self::assertSame([
            'upper', 'lower',
            'upper', 'lower'
        ], $trace);
    }

    public function test_should_retrieve_it_data_as_array(): void
    {
        $list = new Collection(10, 11, 12);
        self::assertSame([10, 11, 12], $list->toArray());
    }

    public function test_should_retrieve_an_array_after_a_transformation(): void
    {
        $list = new Collection('foo', 'bar');
        self::assertSame(
            ['FOO', 'BAR'],
            $list->map(fn($word) => strtoupper($word))->toArray(),
        );
    }

    public function test_should_retrieve_as_array_when_collection_is_initialized_with_one_parameter(): void
    {
        self::assertSame(
            ['foo'],
            (new Collection('foo'))->toArray(),
        );
    }

    public function test_should_retrieve_as_array_when_collection_is_initialized_with_an_array(): void
    {
        self::assertSame(
            ['foo', 'bar'],
            (new Collection(['foo', 'bar']))->toArray(),
        );
    }

    public function test_should_be_iterable(): void
    {
        self::assertSame(
            ['foo', 'bar'],
            iterator_to_array(new Collection(['foo', 'bar'])),
        );

        $list = new Collection('foo', 'bar');
        self::assertSame(
            ['FOO', 'BAR'],
            (iterator_to_array($list->map(fn($word) => strtoupper($word))))
        );

    }

    public function test_should_be_fetch_multiple_times_and_resolve_as_later_as_possible(): void
    {
        $collection = new Collection(12, 13, 14);
        $x2multiplier = $collection->map(fn($base) => $base * 2);
        $x4multiplier = $x2multiplier->map(fn($base) => $base * 2);

        // At this time, x2 and x4 are both generators, x4 contains x2 generator
        self::assertInstanceOf(Generator::class, $x2multiplier->getIterator());
        self::assertInstanceOf(Generator::class, $x4multiplier->getIterator());

        // Here x2 generator is resolved due to toArray(), x4 sill a generator that contains resolve x2
        self::assertSame([24, 26, 28], $x2multiplier->toArray());
        self::assertInstanceOf(ArrayIterator::class, $x2multiplier->getIterator());
        self::assertInstanceOf(Generator::class, $x4multiplier->getIterator());

        // So, x2 can always be recovered
        self::assertSame([24, 26, 28], $x2multiplier->toArray());
        self::assertInstanceOf(ArrayIterator::class, $x2multiplier->getIterator());

        // 4x also can be resolved multiple time
        self::assertInstanceOf(Generator::class, $x4multiplier->getIterator());
        self::assertSame([48, 52, 56], $x4multiplier->toArray());
        self::assertInstanceOf(ArrayIterator::class, $x4multiplier->getIterator());
        self::assertSame([48, 52, 56], $x4multiplier->toArray());
        self::assertInstanceOf(ArrayIterator::class, $x4multiplier->getIterator());

        // x2 still recoverable
        self::assertSame([24, 26, 28], $x2multiplier->toArray());
        self::assertInstanceOf(ArrayIterator::class, $x2multiplier->getIterator());
    }
}
