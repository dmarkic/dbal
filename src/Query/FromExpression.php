<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Blrf\Dbal\QueryBuilderInterface;
use ValueError;
use preg_match;
use is_array;
use strlen;

/**
 * FROM [expression]
 *
 * @phpstan-type FromFromArray array{
 *      expression: string|array<mixed>,
 *      alias?: string|null
 * }
 *
 * @phpstan-type FromToArray array{
 *      expression: string|array<mixed>,
 *      alias: string|null
 * }
 */
class FromExpression extends Expression
{
    /**
     * From expression
     *
     * QueryBuilderInterface enabled subquery
     */
    final public function __construct(
        public readonly string|QueryBuilderInterface $expression,
        public readonly ?string $alias = null
    ) {
        if (is_string($expression) && strlen($expression) == 0) {
            throw new ValueError('Expression cannot be empty');
        }
        if ($expression instanceof QueryBuilderInterface && empty($alias)) {
            throw new ValueError('Expression is QueryBuilder with empty alias');
        }
    }

    public function __toString(): string
    {
        if ($this->expression instanceof QueryBuilderInterface) {
            return '(' . $this->expression->getSql() . ') AS ' . $this->alias;
        }
        return $this->expression . ($this->alias === null ? '' : ' AS ' . $this->alias);
    }

    /** @return FromToArray */
    public function toArray(): array
    {
        return [
            'expression'    => (
                $this->expression instanceof QueryBuilderInterface ? $this->expression->toArray() : $this->expression
            ),
            'alias'         => $this->alias
        ];
    }
}
