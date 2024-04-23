<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Countable;
use Iterator;
use count;

/**
 * Result class
 *
 * @implements Iterator<int, array<mixed>>
 */
class Result implements Countable, Iterator
{
    protected int $position = 0;

    /**
     * Constructor
     *
     * @param array<array<mixed>> $rows Result rows
     */
    public function __construct(
        public readonly array $rows = [],
        public readonly ?int $insertId = null,
        public readonly int $affectedRows = 0,
        public readonly int $warningCount = 0
    ) {
    }

    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * ITERATOR METHODS
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): array
    {
        return $this->rows[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->rows[$this->position]);
    }
}
