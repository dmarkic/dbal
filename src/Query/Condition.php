<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;
use ValueError;

/**
 * Single condition for WHERE or HAVING
 *
 * @phpstan-type ConditionToArray array{
 *      expression: string,
 *      operator: string,
 *      value: mixed
 * }
 *
 * @phpstan-type ConditionFromArray array{
 *      expression: string,
 *      operator: string,
 *      value: mixed
 * }
 */
class Condition implements Stringable
{
    /** @var array<string> */
    protected static $noValueOperators = [
        'is null', 'is not null'
    ];

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
     *
     * @param list{string,string,string}|array{expression?:string, operator?:string, value?:string}|array{'OR': array<array<mixed>>}|array{'AND': array<array<mixed>>} $data
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
                return ConditionGroup::fromArray($data); /* @phpstan-ignore-line */
            }
            $expression = $data['expression'] ?? null;
            $operator = $data['operator'] ?? '=';
            $value = $data['value'] ?? '?';
            if ($expression === null) {
                throw new ValueError('Array should have atleast expression key');
            }
        }
        if (!is_string($expression)) {
            throw new ValueError('expression should be a string');
        }
        if (!is_string($operator)) {
            throw new ValueError('operator should be a string');
        }
        if ($value !== null && !is_string($value)) {
            throw new ValueError('value should be a string');
        }
        return new static($expression, $operator, $value);
    }

    /**
     * Value will be null if operator is in noValueOperators
     */
    public readonly ?string $value;

    final public function __construct(
        public readonly string $expression,
        public readonly string $operator = '=',
        ?string $value = '?'
    ) {
        if (in_array(strtolower($operator), static::$noValueOperators)) {
            $value = null;
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->expression . ' ' . $this->operator . ($this->value === null ? '' : ' ' . $this->value);
    }

    /** @return ConditionToArray */
    public function toArray(): array
    {
        return [
            'expression'    => $this->expression,
            'operator'      => $this->operator,
            'value'         => $this->value
        ];
    }
}
