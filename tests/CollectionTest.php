<?php

declare(strict_types=1);

use Camillebaronnet\Collection\Collection;
use Camillebaronnet\Collection\CollectionGroup;
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

    public function test_should_reduce_data(): void
    {
        self::assertSame(
            50,
            (new Collection(15, 35))->reduce(fn($a, $b) => $a + $b, 0),
        );
    }

    public function test_should_reduce_data_using_keys(): void
    {
        self::assertSame(
            50,
            (new Collection([15 => '', 35 => '']))->reduce(fn($a, $_, $b) => $a + $b, 0),
        );
    }

    public function test_should_filter_data(): void
    {
        self::assertSame(
            [15, 10],
            (new Collection([15, 35, 10]))->filter(fn($value) => $value < 20)->toArray(),
        );
    }

    public function test_filter_should_be_immutable(): void
    {
        $collection = new Collection([15, 35, 10]);

        self::assertSame([15, 10], $collection->filter(fn($value) => $value < 20)->toArray());
        self::assertSame([15, 35], $collection->filter(fn($value) => $value > 10)->toArray());
    }

    public function test_should_sum_elements(): void
    {
        self::assertSame(
            50.4,
            (new Collection([15.4, 35]))->sum(),
        );
    }

    public function test_should_avg_elements(): void
    {
        self::assertSame(
            25.5,
            (new Collection([30,15, 15, 42]))->avg(),
        );
    }

    public function test_should_fetch_unique_elements(): void
    {
        self::assertSame(
            ['foo', 'bar', 'buzz'],
            (new Collection(['foo', 'bar', 'foo', 'bar', 'bar', 'buzz']))->unique()->toArray(),
        );
    }

    public function test_should_group_elements(): void
    {
        $result = (new Collection([
            ['key1' => 'foo', 'key2' => 10],
            ['key1' => 'bar', 'key2' => 11],
            ['key1' => 'foo', 'key2' => 12],
            ['key1' => 'bar', 'key2' => 14],
        ]))->groupBy(fn($x) => $x['key1'])->toArray();

        self::assertCount(2, $result);

        // Validate "foo" group
        self::assertInstanceOf(CollectionGroup::class, $result[0]);
        self::assertEquals('foo', $result[0]->key);
        self::assertEquals([
            ['key1' => 'foo', 'key2' => 10],
            ['key1' => 'foo', 'key2' => 12],
        ], iterator_to_array($result[0]));


        // Validate "bar" group
        self::assertInstanceOf(CollectionGroup::class, $result[1]);
        self::assertEquals('bar', $result[1]->key);
        self::assertEquals([
            ['key1' => 'bar', 'key2' => 11],
            ['key1' => 'bar', 'key2' => 14],
        ], iterator_to_array($result[1]));
    }

    public function test_group_should_support_iteration_before(): void
    {
        $result = (new Collection([
            ['key1' => 'foo', 'key2' => 10],
            ['key1' => 'bar', 'key2' => 11],
            ['key1' => 'foo', 'key2' => 12],
            ['key1' => 'bar', 'key2' => 14],
        ]))->filter(fn() => true)->groupBy(fn($x) => $x['key1'])->toArray();

        // Validate "foo" group
        self::assertInstanceOf(CollectionGroup::class, $result[0]);
        self::assertEquals('foo', $result[0]->key);
        self::assertEquals([
            ['key1' => 'foo', 'key2' => 10],
            ['key1' => 'foo', 'key2' => 12],
        ], iterator_to_array($result[0]));
    }

    public function test_should_be_countable(): void
    {
        $collection = new Collection(10, 20, 30);
        self::assertInstanceOf(Countable::class, $collection);
        self::assertCount(3, $collection);
    }

    public function test_should_be_countable_after_operation_is_applied(): void
    {
        $collection = (new Collection(10, 20, 30))->filter(fn(int $number) => $number > 15);

        self::assertInstanceOf(Countable::class, $collection);
        self::assertCount(2, $collection);
    }

    public function test_should_have_an_internal_iterator(): void
    {
        $logs = [];
        $collection = new Collection(10, 20, 30);
        $collection->each(function ($element) use (&$logs) {
            $logs[] = $element;
        });

        self::assertSame([10, 20, 30], $logs);
    }

    public function test_internal_iterator_should_stop_when_it_receive_false(): void
    {
        $logs = [];
        $collection = new Collection(10, 20, 30);
        $collection->each(function ($element) use (&$logs) {
            $logs[] = $element;
            if($element === 20) {
                return false;
            }
        });

        self::assertSame([10, 20], $logs);
    }

    public function test_internal_iterator_should_support_keys(): void
    {
        $logs = [];
        $collection = new Collection(['foo' => 10, 'bar' => 20]);
        $collection->each(function ($element, $key) use (&$logs) {
            $logs[] = $key;
        });

        self::assertSame(['foo', 'bar'], $logs);
    }

    public function test_should_flatten(): void
    {
        $collection = new Collection([10, 20], [30, 40], [50, 60]);
        self::assertSame([10, 20, 30, 40, 50, 60], $collection->flatten()->toArray());
    }

    public function test_flatten_should_be_immutable(): void
    {
        $collection = new Collection([10, 20], [30, 40], [50, 60]);
        $collection->flatten()->toArray();
        self::assertSame([[10, 20], [30, 40], [50, 60]], $collection->toArray());
    }
}
