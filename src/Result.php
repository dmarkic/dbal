<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Countable;

class Result implements Countable
{
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
}
