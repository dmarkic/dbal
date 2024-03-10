<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;
use implode;
use is_string;

/**
 * Group of conditions
 */
class ConditionGroup implements Stringable
{
    public readonly ConditionType $type;
    protected array $conditions = [];

    public function __construct(
        ConditionType|string $type,
        self|Condition ...$condition
    ) {
        $this->type = (is_string($type) ? ConditionType::from($type) : $type);
        $this->conditions = $condition;
    }

    public function __toString(): string
    {
        return '(' . implode(' ' . $this->type->value . ' ', $this->conditions) . ')';
    }
}
