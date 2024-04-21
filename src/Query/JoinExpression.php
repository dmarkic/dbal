<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use ValueError;

/**
 * @phpstan-type JoinFromArray array{
 *      table: string,
 *      on: string,
 *      alias?: string|null,
 *      type?: string|JoinType
 * }
 */
class JoinExpression extends Expression
{
    public readonly JoinType $type;

    /**
     * @param JoinFromArray $data
     */
    public static function fromArray(array $data): static
    {
        $table = $data['table'] ?? '';
        $on = $data['on'] ?? '';
        $alias = $data['alias'] ?? null;
        $type = $data['type'] ?? JoinType::INNER;
        return new static($type, $table, $on, $alias);
    }

    public static function fromString(string $join): static
    {
        throw new \Exception('Not implemented');
    }

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

    /** @return array{
     *      table: string,
     *      on: string,
     *      alias: string|null,
     *      type: string
     * }
     */
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
