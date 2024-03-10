<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;

/**
 * Single condition for WHERE or HAVING
 */
class Condition implements Stringable
{
    public function __construct(
        public readonly string $expression,
        public readonly string $operator = '=',
        public readonly string $value = '?'
    ) {
    }

    public function __toString(): string
    {
        return $this->expression . ' ' . $this->operator . ' ' . $this->value;
    }
}
