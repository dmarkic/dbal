<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use ValueError;
use preg_match;
use strtoupper;

/**
 * ORDER BY [expression]
 *
 * @phpstan-type OrderByFromArray array{
 *      expression: string,
 *      type?: string|OrderByType
 * }
 *
 * @phpstan-type OrderByToArray array{
 *      expression: string,
 *      type: string
 * }
 */
class OrderByExpression extends Expression
{
    public readonly OrderByType $type;

    final public function __construct(
        public readonly string $expression,
        OrderByType|string $type = 'ASC'
    ) {
        if (strlen($this->expression) == 0) {
            throw new ValueError('Expression cannot be empty');
        }
        if (is_string($type)) {
            $type = empty($type) ? OrderByType::ASC : OrderByType::from(strtoupper($type));
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->expression . ' ' . $this->type->value;
    }

    /** @return OrderByToArray */
    public function toArray(): array
    {
        return [
            'expression' => $this->expression,
            'type' => $this->type->value
        ];
    }
}
