<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use ValueError;
use preg_match;
use strtoupper;

/**
 * ORDER BY [expression]
 */
class OrderByExpression extends Expression
{
    public readonly OrderByType $type;
    /**
     * Create order by expression from array
     *
     * Data keys:
     *
     * - expression: string
     * - type: ?string (OrderByType)
     */
    public static function fromArray(array $data): static
    {
        return new static($data['expression'] ?? '', $data['type'] ?? OrderByType::ASC);
    }

    /**
     * Create select expression from string
     *
     * @note regexp is very basic
     */
    public static function fromString(string $string): static
    {
        preg_match('/([^\s]+)\s?(ASC|DESC)?/ui', $string, $matches);
        $expression = $matches[1];
        $type = strtoupper($matches[2] ?? 'ASC');
        return new static($expression, $type);
    }

    public function __construct(
        public readonly string $expression,
        OrderByType|string $type = 'ASC'
    ) {
        if (strlen($this->expression) == 0) {
            throw new ValueError('Expression cannot be empty');
        }
        if (is_string($type)) {
            $type = empty($type) ? OrderByType::ASC : OrderByType::from($type);
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->expression . ' ' . $this->type->value;
    }

    public function toArray(): array
    {
        return ['expression' => $this->expression, 'type' => $this->type->value];
    }
}
