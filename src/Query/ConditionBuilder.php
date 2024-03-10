<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

/**
 * Condition builder
 *
 * Add more operator methods.
 */
class ConditionBuilder
{
    /**
     * Start grouped AND condition
     */
    public function and(Condition|ConditionGroup ...$condition)
    {
        return new ConditionGroup(ConditionType::AND, ...$condition);
    }

    /**
     * Start grouped OR condition
     */
    public function or(Condition|ConditionGroup ...$condition)
    {
        return new ConditionGroup(ConditionType::OR, ...$condition);
    }

    public function condition(string $expression, string $operator, string $value = '?'): Condition
    {
        return new Condition($expression, $operator, $value);
    }

    public function eq(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '=', $value);
    }
}
