<?php

declare(strict_types=1);

namespace Camillebaronnet\Collection;

final class CollectionGroup extends Collection
{
    public function __construct(
        public mixed $key,
        iterable $elements,
    ) {
        parent::__construct($elements);
    }
}
