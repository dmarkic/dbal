<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionGroup;
use Blrf\Dbal\Query\FromExpression;
use Blrf\Dbal\Query\JoinExpression;
use Blrf\Dbal\Query\JoinType;
use Blrf\Dbal\Query\OrderByExpression;
use Blrf\Dbal\Query\OrderByType;
use Blrf\Dbal\Query\SelectExpression;

interface QueryBuilderInterface
{
    /**
     * Create query builder from array
     *
     * @param array<mixed> $data
     */
    public static function fromArray(array $data, mixed ...$arguments): self;

    public function select(string|SelectExpression ...$exprs): static;

    public function update(string|self $from): static;

    public function insert(string $into): static;

    public function delete(string|self $from): static;

    public function from(string|FromExpression|self $from, string $as = null): static;

    public function addFromExpression(FromExpression $expr): static;

    public function join(string $table, string $on, string $alias = null, JoinType $type = JoinType::INNER): static;

    public function leftJoin(string $table, string $on, string $alias = null): static;
    public function rightJoin(string $table, string $on, string $alias = null): static;
    public function fullJoin(string $table, string $on, string $alias = null): static;

    public function addJoinExpression(JoinExpression $expr): static;

    public function value(string $column, mixed $value): static;

    /**
     * Add values for insert or update
     *
     * @param array<string, mixed> $values [ column => value, ...]
     */
    public function values(array $values): static;

    public function where(Condition|ConditionGroup|callable $condition): static;

    public function andWhere(Condition|ConditionGroup|callable $condition): static;

    public function orWhere(Condition|ConditionGroup|callable $condition): static;

    public function orderBy(string $orderBy, OrderByType|string $type = 'ASC'): static;

    public function addOrderByExpression(OrderByExpression $expr): static;

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

    /**
     * To array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public function getSql(): string;
}
