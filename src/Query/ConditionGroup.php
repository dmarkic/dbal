<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;
use implode;
use is_string;
use ValueError;

/**
 * Group of conditions
 */
class ConditionGroup implements Stringable
{
    public readonly ConditionType $type;
    protected array $conditions = [];

    /**
     * Create condition group from array
     *
     * ```php
     * [
     *   'type' => [ condition, ... ]
     * ]
     */
    public static function fromArray(array $data): static
    {
        $conditions = [];
        reset($data);
        $type = key($data);
        if (is_string($type) && ConditionType::tryFrom(strtoupper($type))) {
            $type = strtoupper($type);
        } else {
            throw new ValueError('Expected first key type: AND or OR');
        }
        $type = ConditionType::from($type);
        foreach ($data[$type->value] as $key => $value) {
            $conditions[] = Condition::fromArray($value);
        }
        return new static($type, ...$conditions);
    }

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

    /**
     * Convert to array
     *
     * ```php
     * [
     *   'type' => [ condition, ... ]
     * ]
     * ```
     */
    public function toArray(): array
    {
        return [
            $this->type->value => array_map(
                fn(Condition|ConditionGroup $c) => $c->toArray(),
                $this->conditions
            )
        ];
    }
}
