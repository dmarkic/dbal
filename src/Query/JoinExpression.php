<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use ValueError;

/**
 * JOIN [expression]
 *
 * @phpstan-type JoinFromArray array{
 *      table: string,
 *      on: string,
 *      alias?: string|null,
 *      type?: string|JoinType
 * }
 *
 * @phpstan-type JoinToArray array{
 *      table: string,
 *      on: string,
 *      alias: string|null,
 *      type: string
 * }
 */
class JoinExpression extends Expression
{
    public readonly JoinType $type;

    final public function __construct(
        JoinType|string $type,
        public readonly string $table,
        public readonly string $on,
        public readonly ?string $alias = null
    ) {
        if (is_string($type)) {
            $type = empty($type) ? JoinType::INNER : JoinType::from(strtoupper($type));
        }
        if (strlen($table) == 0) {
            throw new ValueError('Join expression table cannot be empty');
        }
        if (strlen($on) == 0) {
            throw new ValueError('Join expression ON cannot be empty');
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type->value . ' JOIN ' . $this->table .
               ($this->alias === null ? '' : ' AS ' . $this->alias) .
               ' ON ' . $this->on;
    }

    /** @return JoinToArray **/
    public function toArray(): array
    {
        return [
            'type'      => $this->type->value,
            'table'     => $this->table,
            'on'        => $this->on,
            'alias'     => $this->alias
        ];
    }
}
