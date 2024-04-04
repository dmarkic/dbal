<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\ConditionGroup;
use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\Limit;
use Blrf\Dbal\Query\FromExpression;
use Blrf\Dbal\Query\SelectExpression;
use Blrf\Dbal\Query\OrderByExpression;
use Blrf\Dbal\Query\OrderByType;
use Blrf\Dbal\Query\Type;
use TypeError;
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
    protected Condition|ConditionGroup|null $where = null;
    /**
     * Order by expressions
     * @var array<OrderByExpression>
     */
    protected array $orderBy = [];
    protected ?Limit $limit = null;
    /**
     * Parameters
     * @var array<int|non-int-string, mixed>
     */
    protected array $parameters = [];

    /**
     * Create query builder from array
     *
     * # Array keys
     *
     * ## `class` string
     *
     * Which class to use to construct query builder (defaults to static class)
     *
     * ## `type` string
     *
     * Type of query (default: SELECT)
     *
     * ## `select` string|array
     *
     * Select expressions
     *
     * String: EXPRESSION AS ALIAS, EXPRESSION AS ALIAS, ...
     * Array: [['expression' => 'EXPRESSION', 'alias' => 'ALIAS'], ...]
     *
     * ## `from` string|array|QueryBuilder
     *
     * From expressions
     *
     * String: NA; TBD!
     * Array: [['expression' => 'EXPRESSION'], 'alias' => 'ALIAS']
     * Array with subquery: [['expression' => QueryBuilder::toArray(), 'alias' => 'ALIAS']]
     *
     * ## `columns` array
     *
     * List of `INSERT` or `UPDATE` columns
     *
     * ## `where` Condition|ConditionGroup
     *
     * Simple condition: ['ex', 'op', 'value'] or
     * ['expression' => 'ex', 'operator' => 'op', 'value' => 'value']
     *
     * Group condition: ['TYPE' => [... conditions ... ] ]
     *
     *
     * ## `orderBy` array
     *
     * List of `ORDER BY` columns
     *
     * ## `limit` array
     *
     * String: 'LIMIT l OFFSET o'
     * Array: ['limit' => limit, 'offset' => offset] or $data['limit'], $data['offset']
     *
     * ## `parameters` array
     *
     * List of parameters
     *
     * @param array{
     *      class?: string,
     *      type?: string|Type,
     *      select?: array<mixed>|string,
     *      from?: array<mixed>|string,
     *      columns?: array<string>,
     *      where?:array<mixed>|null,
     *      values?:array<string,mixed>,
     *      orderBy?:array<mixed>|string,
     *      limit?:array{limit?: int|null, offset?: int|null}|int|null,
     *      offset?:int
     *  } $data
     * @param mixed $arguments Arguments to QueryBuilder constructor
     */
    public static function fromArray(array $data, mixed ...$arguments): QueryBuilder|QueryBuilderInterface
    {
        $class = $data['class'] ?? static::class;
        $qb = new $class(...$arguments);
        if (!($qb instanceof QueryBuilder)) {
            throw new TypeError('Provided class is not instance of QueryBuilder');
        }
        if (isset($data['type'])) {
            $qb->setType($data['type']);
        }
        /**
         * SELECT expressions
         */
        if (isset($data['select'])) {
            if (is_array($data['select'])) {
                foreach ($data['select'] as $select) {
                    if (is_array($select)) {
                        $qb->addSelectExpression(SelectExpression::fromArray($select));
                    }
                    if (is_string($select)) {
                        $qb->addSelectExpression(SelectExpression::fromString($select));
                    }
                }
            }
            if (is_string($data['select'])) {
                foreach (explode(',', $data['select']) as $select) {
                    $qb->addSelectExpression(SelectExpression::fromString($select));
                }
            }
        }
        /**
         * FROM expressions
         * @note string types will cause problem with subqueries
         */
        if (isset($data['from'])) {
            if (is_array($data['from'])) {
                foreach ($data['from'] as $from) {
                    if (is_array($from)) {
                        $qb->addFromExpression(FromExpression::fromArray($from));
                    } elseif (is_string($from)) {
                        $qb->addFromExpression(FromExpression::fromString($from));
                    }
                }
            } elseif (is_string($data['from'])) {
                $qb->addFromExpression(FromExpression::fromString($data['from']));
            }
        }
        $qb->columns = $data['columns'] ?? [];
        if (isset($data['where']) && is_array($data['where'])) {
            $qb->where  = Condition::fromArray($data['where']);
        }

        /**
         * orderBy expressions
         */
        if (isset($data['orderBy'])) {
            if (is_array($data['orderBy'])) {
                foreach ($data['orderBy'] as $orderBy) {
                    if (is_array($orderBy)) {
                        $qb->addOrderByExpression(OrderByExpression::fromArray($orderBy));
                    } elseif (is_string($orderBy)) {
                        $qb->addOrderbyExpression(OrderByExpression::fromString($orderBy));
                    }
                }
            } elseif (is_string($data['orderBy'])) {
                $qb->addOrderByExpression(OrderByExpression::fromString($data['orderBy']));
            }
        }

        if (isset($data['limit'])) {
            if (is_array($data['limit'])) {
                $qb->limit = Limit::fromArray($data['limit']);
            } else {
                /**
                 * Limit may also be provided directly in data. So does offset.
                 */
                $limit = [
                    'limit' => $data['limit'],
                    'offset' => $data['offset'] ?? null
                ];
                $qb->limit = Limit::fromArray($limit);
            }
        }
        $qb->parameters = $data['parameters'] ?? [];
        return $qb;
    }

    /**
     * @return array{
     *      class: string,
     *      type: string,
     *      select: array<mixed>,
     *      from: array<mixed>,
     *      columns: string[],
     *      where: null|array<mixed>,
     *      orderBy: array<mixed>,
     *      limit: array{limit?: int|null, offset?: int|null}|null,
     *      parameters: array<int|non-int-string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'class'     => $this::class,
            'type'      => $this->type->value,
            'select'    => array_map(fn($expr) => $expr->toArray(), $this->select),
            'from'      => array_map(fn($expr) => $expr->toArray(), $this->from),
            'columns'   => $this->columns,
            'where'     => $this->where === null ? null : $this->where->toArray(),
            'orderBy'   => array_map(fn($expr) => $expr->toArray(), $this->orderBy),
            'limit'     => ($this->limit === null ? null : $this->limit->toArray()),
            'parameters'    => $this->parameters
        ];
    }

    /**
     * Start select query
     *
     * ```php
     * $qb->select('colA as A', 'colB as B');
     * ```
     *
     * @see self::createSelectExpression
     */
    public function select(string ...$exprs): static
    {
        $this->setType(Type::SELECT);
        foreach ($exprs as $expr) {
            $this->addSelectExpression($this->createSelectExpression($expr));
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
    public function update(string|QueryBuilderInterface $from, string $as = null): static
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
    public function insert(string $into, string $as = null): static
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
    public function delete(string|QueryBuilderInterface $from, string $as = null): static
    {
        $this->setType(Type::DELETE);
        return $this->from($from, $as);
    }

    /**
     * From
     *
     * @see self::createFromExpression()
     */
    public function from(string|QueryBuilderInterface $from, string $as = null): static
    {
        return $this->addFromExpression($this->createFromExpression($from, $as));
    }

    /**
     * Into
     *
     * Alias for self::from()
     *
     * @see self::createFromExpression()
     */
    public function into(string $from, string $as = null): static
    {
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
        }
        $this->where = $condition;
        return $this;
    }

    public function andWhere(Condition|ConditionGroup|callable $condition): static
    {
        if (is_callable($condition)) {
            $condition = $condition(new ConditionBuilder());
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
                       $this->getSqlPartWhere() .
                       $this->getSqlPartOrderBy() .
                       $this->getSqlPartLimit();
            case Type::UPDATE:
                return $this->type->value .
                       $this->getSqlPartTable() .
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
            default:
                // @codeCoverageIgnoreStart
                throw new TypeError('Unknown type: ' . $this->type->value);
                // @codeCoverageIgnoreEnd
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
        $this->select = $this->from = $this->columns = $this->parameters = $this->orderBy = [];
        $this->limit = $this->where = null;
        return $this;
    }

    /**
     * Create new select expression object
     *
     * @see self::select()
     */
    protected function createSelectExpression(string $expr): SelectExpression
    {
        return new SelectExpression($expr);
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
    protected function createFromExpression(string|QueryBuilderInterface $from, string $as = null): FromExpression
    {
        return new FromExpression($from, $as);
    }

    public function addFromExpression(FromExpression $expr): static
    {
        $this->from[] = $expr;
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
    protected function createLimit(?int $limit, ?int $offset): Limit
    {
        return new Limit($limit, $offset);
    }
}
