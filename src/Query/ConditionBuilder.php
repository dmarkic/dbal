<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

/**
 * Condition builder
 *
 */
class ConditionBuilder
{
    /**
     * Start grouped AND condition
     */
    public function and(Condition|ConditionGroup ...$condition): ConditionGroup
    {
        return new ConditionGroup(ConditionType::AND, ...$condition);
    }

    /**
     * Start grouped OR condition
     */
    public function or(Condition|ConditionGroup ...$condition): ConditionGroup
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

    public function neq(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '<>', $value);
    }

    public function lt(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '<', $value);
    }

    public function lte(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '<=', $value);
    }

    public function gt(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '>', $value);
    }

    public function gte(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, '>=', $value);
    }

    public function isNull(string $expression): Condition
    {
        return $this->condition($expression, 'IS NULL');
    }

    public function isNotNull(string $expression): Condition
    {
        return $this->condition($expression, 'IS NOT NULL');
    }

    public function like(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, 'LIKE', $value);
    }

    public function notLike(string $expression, string $value = '?'): Condition
    {
        return $this->condition($expression, 'NOT LIKE', $value);
    }
}
