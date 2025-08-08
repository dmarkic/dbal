<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\ConditionGroup;
use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\Limit;
use Blrf\Dbal\Query\FromExpression;
use Blrf\Dbal\Query\SelectExpression;
use Blrf\Dbal\Query\JoinExpression;
use Blrf\Dbal\Query\JoinType;
use Blrf\Dbal\Query\OrderByExpression;
use Blrf\Dbal\Query\OrderByType;
use Blrf\Dbal\Query\Type;
use Blrf\Dbal\Driver\QueryBuilder as DriverQueryBuilder;
use TypeError;
use ValueError;
use array_map;
use is_array;
use is_string;
use explode;
use implode;

/**
 * SQL Query builder
 *
 * Each Driver defines it's own QueryBuilder which may override any method to implement it's own SQL dialect.
 *
 * NOTE: It will not validate queries.
 *
 * @phpstan-import-type QueryBuilderFromArray from QueryBuilderInterface
 * @phpstan-import-type QueryBuilderToArray from QueryBuilderInterface
 *
 * @phpstan-import-type SelectFromArray from SelectExpression
 */
class QueryBuilder implements QueryBuilderInterface
{
    protected Type $type = Type::SELECT;
    /**
     * Select expressions
     * @var array<SelectExpression>
     */
    protected array $select = [];
    /**
     * From expressions
     * @var array<FromExpression>
     */
    protected array $from = [];
    /**
     * Insert or update columns
     *
     * @var array<string>
     */
    protected array $columns = [];
    /**
     * Joins
     * @var array<JoinExpression>
     */
    protected array $join = [];
    /**
     * Where conditions
     */
    protected Condition|ConditionGroup|null $where = null;
    /**
     * Order by expressions
     * @var array<OrderByExpression>
     */
    protected array $orderBy = [];
    /**
     * Limit
     */
    protected ?Limit $limit = null;
    /**
     * Parameters
     * @var array<int|non-int-string, mixed>
     */
    protected array $parameters = [];
    /**
     * Quote char
     *
     * When you want to quote identifier use self::quoteIdentifier() method.
     * @note DBAL will not quote identifiers
     */
    protected string $quoteChar = '`';

    /**
     * Build query from array
     *
     * @param QueryBuilderFromArray $data
     */
    public function fromArray(array $data): static
    {
        if (!isset($data['type'])) {
            throw new ValueError('Missing type');
        }
        $this->setType($data['type']);

        /**
         * Select expressions
         */
        if (isset($data['select']) && is_array($data['select'])) {
            foreach ($data['select'] as $expr) {
                $this->select(...$data['select']);
            }
        }

        /**
         * From expressions
         */
        if (isset($data['from']) && is_array($data['from'])) {
            foreach ($data['from'] as $fromExpr) {
                if (is_array($fromExpr)) {
                    $expr = $fromExpr['expression'] ?? '';
                    if (is_array($expr)) {
                        $qb = clone $this;
                        // @phpstan-ignore-next-line
                        $qb->fromArray($expr);
                        $expr = $qb;
                    }
                    $this->addFromExpression(
                        $this->createFromExpression($expr, $fromExpr['alias'] ?? null)
                    );
                } elseif ($fromExpr instanceof FromExpression) {
                    $this->addFromExpression($fromExpr);
                }
            }
        }

        /**
         * Join expressions
         */
        if (isset($data['join']) && is_array($data['join'])) {
            foreach ($data['join'] as $joinExpr) {
                if (is_array($joinExpr)) {
                    $this->addJoinExpression(
                        $this->createJoinExpression(
                            $joinExpr['type'] ?? JoinType::INNER,
                            $joinExpr['table'] ?? '',
                            $joinExpr['on'] ?? '',
                            $joinExpr['alias'] ?? null,
                        )
                    );
                } elseif ($joinExpr instanceof JoinExpression) {
                    $this->addJoinExpression($joinExpr);
                }
            }
        }

        /**
         * Columns
         */
        if (isset($data['columns']) && is_array($data['columns'])) {
            $this->columns = $data['columns'];
        }

        /**
         * Where conditions
         */
        if (isset($data['where']) && is_array($data['where'])) {
            $this->where  = Condition::fromArray($data['where']);
        }

        /**
         * Order by expressions
         */
        if (isset($data['orderBy']) && is_array($data['orderBy'])) {
            foreach ($data['orderBy'] as $orderByExpr) {
                if (is_array($orderByExpr)) {
                    $this->addOrderByExpression(
                        $this->createOrderByExpression(
                            $orderByExpr['expression'] ?? '',
                            $orderByExpr['type'] ?? 'ASC'
                        )
                    );
                } elseif ($orderByExpr instanceof OrderByExpression) {
                    $this->addOrderByExpression($orderByExpr);
                }
            }
        }

        /**
         * Limit
         */
        if (isset($data['limit'])) {
            if (is_array($data['limit'])) {
                $this->limit = $this->createLimit($data['limit']['limit'] ?? null, $data['limit']['offset'] ?? null);
            } else {
                /**
                 * Limit may also be provided directly in data. So does offset.
                 */
                $this->limit = $this->createLimit($data['limit'] ?? null, $data['offset'] ?? null);
            }
        }

        /**
         * Parameters
         */
        if (isset($data['parameters']) && is_array($data['parameters'])) {
            $this->parameters = $data['parameters'];
        }

        return $this;
    }

    /** @return QueryBuilderToArray */
    public function toArray(): array
    {
        return [ /* @phpstan-ignore return.type */
            'type'          => $this->type->value,
            'select'        => array_map(fn($expr) => $expr->toArray(), $this->select),
            'from'          => array_map(fn($expr) => $expr->toArray(), $this->from),
            'join'          => array_map(fn($expr) => $expr->toArray(), $this->join),
            'columns'       => $this->columns,
            'where'         => $this->where === null ? null : $this->where->toArray(),
            'orderBy'       => array_map(fn($expr) => $expr->toArray(), $this->orderBy),
            'limit'         => ($this->limit === null ? null : $this->limit->toArray()),
            'parameters'    => $this->parameters
        ];
    }

    /**
     * Start select query
     *
     * @param string|SelectFromArray|SelectExpression $exprs
     * @see self::createSelectExpression
     */
    public function select(string|array|SelectExpression ...$exprs): static
    {
        $this->setType(Type::SELECT);
        foreach ($exprs as $expr) {
            if ($expr instanceof SelectExpression) {
                $this->addSelectExpression($expr);
            } elseif (is_array($expr)) {
                $this->addSelectExpression(
                    $this->createSelectExpression(
                        $expr['expression'] ?? '',
                        $expr['alias'] ?? null
                    )
                );
            } else {
                $this->addSelectExpression($this->createSelectExpression($expr));
            }
        }
        return $this;
    }

    /**
     * Start update query
     *
     * ```php
     * $qb->update('table')->values(['col' => 'val'])->where(...);
     * ```
     */
    public function update(string|FromExpression|QueryBuilderInterface $from, string $as = null): static
    {
        $this->setType(Type::UPDATE);
        return $this->from($from, $as);
    }

    /**
     * Start insert query
     *
     * ```php
     * $qb->insert('table')->values(['col' => 'val']);
     * ```
     */
    public function insert(string|FromExpression $into, string $as = null): static
    {
        $this->setType(Type::INSERT);
        return $this->into($into, $as);
    }

    /**
     * Start delete query
     *
     * ```php
     * $db->delete('table')->where('id = ?')->setParameters([1]);
     * ```
     */
    public function delete(string|FromExpression|QueryBuilderInterface $from, string $as = null): static
    {
        $this->setType(Type::DELETE);
        return $this->from($from, $as);
    }

    /**
     * From
     *
     * @see self::createFromExpression()
     */
    public function from(string|QueryBuilderInterface|FromExpression $from, string $as = null): static
    {
        if ($from instanceof FromExpression) {
            return $this->addFromExpression($from);
        }
        return $this->addFromExpression($this->createFromExpression($from, $as));
    }

    /**
     * Into
     *
     * Alias for self::from()
     *
     * @see self::createFromExpression()
     */
    public function into(string|FromExpression $from, string $as = null): static
    {
        if ($from instanceof FromExpression) {
            return $this->addFromExpression($from);
        }
        return $this->addFromExpression($this->createFromExpression($from, $as));
    }

    /**
     * Value for update or insert
     */
    public function value(string $column, mixed $value): static
    {
        $this->columns[] = $column;
        $this->addParameter($value);
        return $this;
    }

    /**
     * Add many values for update or insert
     *
     * @param array<string,mixed> $values [[col => val], ...]
     */
    public function values(array $values): static
    {
        foreach ($values as $column => $value) {
            $this->value($column, $value);
        }
        return $this;
    }

    /**
     * Set (self::values() alias)
     *
     * @param array<string,mixed> $values
     */
    public function set(array $values): static
    {
        return $this->values($values);
    }

    public function join(string $table, string $on, string $alias = null, JoinType $type = JoinType::INNER): static
    {
        return $this->addJoinExpression($this->createJoinExpression($type, $table, $on, $alias));
    }

    public function leftJoin(string $table, string $on, string $alias = null): static
    {
        return $this->addJoinExpression($this->createJoinExpression(JoinType::LEFT, $table, $on, $alias));
    }

    public function rightJoin(string $table, string $on, string $alias = null): static
    {
        return $this->addJoinExpression($this->createJoinExpression(JoinType::RIGHT, $table, $on, $alias));
    }

    public function fullJoin(string $table, string $on, string $alias = null): static
    {
        return $this->addJoinExpression($this->createJoinExpression(JoinType::FULL, $table, $on, $alias));
    }

    /**
     * Start condition builder or create simple condition
     *
     * @return ($expression is null ? ConditionBuilder : Condition)
     */
    public function condition(
        string $expression = null,
        string $operator = '=',
        string $value = '?'
    ): Condition|ConditionBuilder {
        if ($expression === null) {
            return new ConditionBuilder();
        }
        return new Condition($expression, $operator, $value);
    }

    /**
     * Where
     *
     * @note Maybe someday, we could accept string and we'd parse it into condition(s)
     */
    public function where(Condition|ConditionGroup|callable $condition): static
    {
        if (is_callable($condition)) {
            $condition = $condition(new ConditionBuilder());
            if (
                !($condition instanceof Condition) &&
                !($condition instanceof ConditionGroup)
            ) {
                throw new ValueError('Closure should return Condition or ConditionGroup');
            }
        }
        $this->where = $condition;
        return $this;
    }

    public function andWhere(Condition|ConditionGroup|callable $condition): static
    {
        if (is_callable($condition)) {
            $condition = $condition(new ConditionBuilder());
            if (
                !($condition instanceof Condition) &&
                !($condition instanceof ConditionGroup)
            ) {
                throw new ValueError('Closure should return Condition or ConditionGroup');
            }
        }
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            // recreate as condition group
            $this->where = new ConditionGroup('AND', $this->where, $condition);
        }
        return $this;
    }

    public function orWhere(Condition|ConditionGroup|callable $condition): static
    {
        if (is_callable($condition)) {
            $condition = $condition(new ConditionBuilder());
            if (
                !($condition instanceof Condition) &&
                !($condition instanceof ConditionGroup)
            ) {
                throw new ValueError('Closure should return Condition or ConditionGroup');
            }
        }
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            // recreate as condition group
            $this->where = new ConditionGroup('OR', $this->where, $condition);
        }
        return $this;
    }

    public function orderBy(OrderByExpression|string $expr, OrderByType|string $type = 'ASC'): static
    {
        if ($expr instanceof OrderByExpression) {
            return $this->addOrderByExpression($expr);
        }
        return $this->addOrderByExpression($this->createOrderByExpression($expr, $type));
    }

    /**
     * Set limit and/or offset
     *
     * @see self::createLimit()
     */
    public function limit(?int $limit = null, ?int $offset = null): static
    {
        $this->limit = $this->createLimit($limit, $offset);
        return $this;
    }

    /**
     * Set parameters
     *
     * @note This will override parameters (maybe added by values() or value())
     */
    public function setParameters(array $params): static
    {
        $this->parameters = $params;
        return $this;
    }

    /**
     * Add parameter
     */
    public function addParameter(mixed ...$param): static
    {
        $this->parameters = [...$this->parameters, ...$param];
        return $this;
    }

    /**
     * Get parameters
     *
     * @return array<int|non-int-string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }


    /**
     * Get SQL query
     *
     * ## Select
     *
     * - self::getSqlPartSelect()
     * - self::getSqlPartFrom()
     * - self::getSqlPartJoin()
     * - self::getSqlPartWhere()
     * - self::getSqlPartOrderBy()
     * - self::getSqlPartLimit()
     *
     * ## Update
     *
     * - self::getSqlPartTable()
     * - self::getSqlPartSet()
     * - self::getSqlPartWhere()
     * - self::getSqlPartOrderBy()
     * - self::getSqlPartLimit()
     *
     * ## Insert
     *
     * - self::getSqlPartInto()
     * - self::getSqlPartColumns()
     * - self::getSqlPartValues()
     *
     * ## Delete
     *
     * - self::getSqlPartFrom()
     * - self::getSqlPartWhere()
     * - self::getSqlPartLimit()
     */
    public function getSql(): string
    {
        switch ($this->type) {
            case Type::SELECT:
                return $this->type->value .
                       $this->getSqlPartSelect() .
                       $this->getSqlPartFrom() .
                       $this->getSqlPartJoin() .
                       $this->getSqlPartWhere() .
                       $this->getSqlPartOrderBy() .
                       $this->getSqlPartLimit();
            case Type::UPDATE:
                return $this->type->value .
                       $this->getSqlPartTable() .
                       $this->getSqlPartJoin() .
                       $this->getSqlPartSet() .
                       $this->getSqlPartWhere() .
                       $this->getSqlPartOrderBy() .
                       $this->getSqlPartLimit();

            case Type::INSERT:
                return $this->type->value .
                       $this->getSqlPartInto() .
                       $this->getSqlPartColumns() .
                       $this->getSqlPartValues();
            case Type::DELETE:
                return $this->type->value .
                       $this->getSqlPartFrom() .
                       $this->getSqlPartWhere() .
                       $this->getSqlPartOrderBy() .
                       $this->getSqlPartLimit();
        }
    }

    /**
     * Select expressions
     */
    protected function getSqlPartSelect(): string
    {
        return ' ' . implode(',', $this->select);
    }

    /**
     * Update table expression(s)
     */
    protected function getSqlPartTable(): string
    {
        return empty($this->from) ? '' : ' ' . implode(', ', $this->from);
    }

    /**
     * Set for UPDATE
     */
    protected function getSqlPartSet(): string
    {
        return ' SET ' . implode(', ', array_map(fn($col) => $col . ' = ?', $this->columns));
    }

    /**
     * From expressions
     */
    protected function getSqlPartFrom(): string
    {
        return empty($this->from) ? '' : ' FROM ' . implode(', ', $this->from);
    }

    protected function getSqlPartJoin(): string
    {
        return empty($this->join) ? '' : ' ' . implode(' ', array_map(fn($join) => (string)$join, $this->join));
    }

    /**
     * Into expressions
     */
    protected function getSqlPartInto(): string
    {
        return empty($this->from) ? '' : ' INTO ' . implode(', ', $this->from);
    }

    /**
     * Columns for insert
     */
    protected function getSqlPartColumns(): string
    {
        return ' (' . implode(', ', $this->columns) . ')';
    }

    protected function getSqlPartValues(): string
    {
        return ' VALUES(' . implode(', ', array_map(fn($col) => '?', $this->columns)) . ')';
    }
    /**
     * Where conditions
     */
    protected function getSqlPartWhere(): string
    {
        return empty($this->where) ? '' : ' WHERE ' . $this->where;
    }

    /**
     * Order by
     */
    protected function getSqlPartOrderBy(): string
    {
        return empty($this->orderBy) ? '' : ' ORDER BY ' . implode(', ', $this->orderBy);
    }

    /**
     * Limit
     */
    protected function getSqlPartLimit(): string
    {
        return empty($this->limit) ? '' : ' ' . (string)$this->limit;
    }

    /**
     * Set query type
     *
     * This method will reset the query builder
     */
    protected function setType(string|Type $type): static
    {
        if (is_string($type)) {
            $type = Type::from($type);
        }
        $this->type = $type;
        $this->reset();
        return $this;
    }

    protected function reset(): static
    {
        $this->select = $this->from = $this->columns = $this->join = $this->parameters = $this->orderBy = [];
        $this->limit = $this->where = null;
        return $this;
    }

    /**
     * Create new select expression object
     *
     * @see self::select()
     */
    public function createSelectExpression(string $expr, string $alias = null): SelectExpression
    {
        return new SelectExpression($expr, $alias);
    }

    /**
     * Add select expression to SELECT query
     */
    public function addSelectExpression(SelectExpression $expr): static
    {
        $this->select[] = $expr;
        return $this;
    }

    /**
     * Create new from expression
     *
     * @see self::from()
     */
    public function createFromExpression(string|QueryBuilderInterface $from, string $as = null): FromExpression
    {
        return new FromExpression($from, $as);
    }

    public function addFromExpression(FromExpression $expr): static
    {
        $this->from[] = $expr;
        return $this;
    }

    public function createJoinExpression(
        string|JoinType $type,
        string $table,
        string $on,
        string $alias = null,
    ): JoinExpression {
        return new JoinExpression($type, $table, $on, $alias);
    }

    public function addJoinExpression(JoinExpression $expr): static
    {
        $this->join[] = $expr;
        return $this;
    }

    public function createOrderByExpression(
        string $expr,
        OrderByType|string $type = 'ASC'
    ): OrderByExpression {
        return new OrderByExpression($expr, $type);
    }

    public function addOrderByExpression(OrderByExpression $expr): static
    {
        $this->orderBy[] = $expr;
        return $this;
    }

    /**
     * Create limit
     *
     * @see self::limit()
     */
    public function createLimit(?int $limit, ?int $offset): Limit
    {
        return new Limit($limit, $offset);
    }

    /**
     * Quote identifier
     */
    public function quoteIdentifier(string $id): string
    {
        if (strpos($id, '.') !== false) {
            return (
                implode(
                    '.',
                    array_map($this->quoteSingleIdentifier(...), explode('.', $id))
                )
            );
        }
        return $this->quoteSingleIdentifier($id);
    }

    public function quoteSingleIdentifier(string $id): string
    {
        return $this->quoteChar . $id . $this->quoteChar;
    }
}
