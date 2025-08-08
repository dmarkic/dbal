<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\ConditionGroup;
use Blrf\Dbal\Query\FromExpression;
use Blrf\Dbal\Query\JoinExpression;
use Blrf\Dbal\Query\JoinType;
use Blrf\Dbal\Query\OrderByExpression;
use Blrf\Dbal\Query\OrderByType;
use Blrf\Dbal\Query\SelectExpression;
use Blrf\Dbal\Query\Limit;

/**
 * Query builder interface
 *
 * @phpstan-import-type SelectFromArray from SelectExpression
 * @phpstan-import-type SelectToArray from SelectExpression
 * @phpstan-import-type FromFromArray from FromExpression
 * @phpstan-import-type FromToArray from FromExpression
 * @phpstan-import-type ConditionFromArray from Condition
 * @phpstan-import-type JoinFromArray from JoinExpression
 * @phpstan-import-type JoinToArray from JoinExpression
 * @phpstan-import-type OrderByFromArray from OrderByExpression
 * @phpstan-import-type OrderByToArray from OrderByExpression
 * @phpstan-import-type LimitFromArray from Limit
 * @phpstan-import-type LimitToArray from Limit
 *
 * @phpstan-type QueryBuilderFromArray array{
 *      type: string,
 *      select?: array<SelectFromArray>|array<SelectExpression>,
 *      from?: array<FromFromArray>|array<FromExpression>,
 *      join?: array<JoinFromArray>|array<JoinExpression>,
 *      columns?: array<string>,
 *      where?: ConditionFromArray|null,
 *      orderBy?: array<OrderByFromArray>|array<OrderByExpression>,
 *      limit?: int|null|LimitFromArray,
 *      offset?: int|null
 * }
 *
 * @phpstan-type QueryBuilderToArray array{
 *      type: string,
 *      select: array<SelectToArray>,
 *      from: array<FromToArray>,
 *      join: array<JoinToArray>,
 *      where: ConditionFromArray|null,
 *      orderBy: array<OrderByToArray>,
 *      limit: null|LimitToArray
 * }
 */
interface QueryBuilderInterface
{
    /**
     * Build query from array
     *
     * @param QueryBuilderFromArray $data
     */
    public function fromArray(array $data): static;
    /**
     * Return query builder as array
     * @return QueryBuilderToArray
     */
    public function toArray(): array;

    /**
     * @param string|SelectFromArray|SelectExpression $exprs
     */
    public function select(string|array|SelectExpression ...$exprs): static;

    public function update(string|FromExpression|self $from): static;

    public function insert(string|FromExpression $into): static;

    public function delete(string|FromExpression|self $from): static;

    public function from(string|FromExpression|self $from, string $as = null): static;

    public function into(string|FromExpression $from, string $as = null): static;

    public function value(string $column, mixed $value): static;

    /**
     * Add values for insert or update
     *
     * @param array<string, mixed> $values [ column => value, ...]
     */
    public function values(array $values): static;

    /**
     * Set (self::values() alias)
     *
     * @param array<string,mixed> $values
     */
    public function set(array $values): static;

    public function join(string $table, string $on, string $alias = null, JoinType $type = JoinType::INNER): static;

    public function leftJoin(string $table, string $on, string $alias = null): static;
    public function rightJoin(string $table, string $on, string $alias = null): static;
    public function fullJoin(string $table, string $on, string $alias = null): static;

    /**
     * Start condition builder or create simple condition
     *
     * @return ($expression is null ? ConditionBuilder : Condition)
     */
    public function condition(
        string $expression = null,
        string $operator = '=',
        string $value = '?'
    ): Condition|ConditionBuilder;

    public function where(Condition|ConditionGroup|callable $condition): static;

    public function andWhere(Condition|ConditionGroup|callable $condition): static;

    public function orWhere(Condition|ConditionGroup|callable $condition): static;

    public function orderBy(string $orderBy, OrderByType|string $type = 'ASC'): static;

    public function limit(?int $offset = null, ?int $limit = null): static;

    /**
     * @param array<int, mixed> $params
     */
    public function setParameters(array $params): static;

    public function addParameter(mixed ...$param): static;

    /**
     * Get parameters
     *
     * @return array<int, mixed>
     */
    public function getParameters(): array;

    public function getSql(): string;

    /**
     * Quote identifier
     */
    public function quoteIdentifier(string $id): string;

    /**
     * Creators
     */
    public function createSelectExpression(string $expr, string $alias = null): SelectExpression;
    public function createFromExpression(string|QueryBuilderInterface $from, string $as = null): FromExpression;
    public function createJoinExpression(
        string|JoinType $type,
        string $table,
        string $on,
        string $alias = null,
    ): JoinExpression;
    public function createOrderByExpression(
        string $expr,
        OrderByType|string $type = 'ASC'
    ): OrderByExpression;
    public function createLimit(?int $limit, ?int $offset): Limit;

    /**
     * Adders
     */
    public function addSelectExpression(SelectExpression $expr): static;
    public function addFromExpression(FromExpression $expr): static;
    public function addJoinExpression(JoinExpression $expr): static;
    public function addOrderByExpression(OrderByExpression $expr): static;
}
