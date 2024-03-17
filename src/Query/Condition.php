<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;
use ValueError;

/**
 * Single condition for WHERE or HAVING
 */
class Condition implements Stringable
{
    /**
     * Create condition from array
     *
     * Expected keys:
     *
     * - expression
     * - operator
     * - value
     *
     * Can also be a list array with 1-3 values (epxression, operator='=', value='?')
     *
     * If first key in data is ConditionType, it will pass the data to ConditionGroup::fromArray. So
     * this method can parse simple and Group conditions.
     */
    public static function fromArray(array $data): static|ConditionGroup
    {
        if (array_is_list($data)) {
            $expression = $data[0] ?? null;
            $operator = $data[1] ?? '=';
            $value = $data[2] ?? '?';
            if ($expression === null) {
                throw new ValueError('Array list should contain alteast expression in key 0');
            }
        } else {
            reset($data);
            $type = key($data);
            if (is_string($type) && ConditionType::tryFrom(strtoupper($type))) {
                return ConditionGroup::fromArray($data);
            }
            $expression = $data['expression'] ?? null;
            $operator = $data['operator'] ?? '=';
            $value = $data['value'] ?? '?';
            if ($expression === null) {
                throw new ValueError('Array should have atleast expression key');
            }
        }
        return new static($expression, $operator, $value);
    }

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

    public function toArray(): array
    {
        return [
            'expression'    => $this->expression,
            'operator'      => $this->operator,
            'value'         => $this->value
        ];
    }
}
