<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionGroup;

interface QueryBuilderInterface
{
    /**
     * Create query builder from array
     */
    public static function fromArray(array $data): static;

    public function select(string ...$exprs): static;

    public function update(string|self $from): static;

    public function insert(string|self $info): static;

    public function delete(string|self $from): static;

    public function from(string|self $from, string $as = null): static;

    public function value(string $column, mixed $value): static;

    public function values(array $values): static;

    public function where(Condition|ConditionGroup|callable $condition): static;

    public function orderBy(string $orderBy, string $type = 'ASC'): static;

    public function limit(?int $offset = null, ?int $limit = null): static;

    public function setParameters(array $params): static;

    public function addParameter(mixed $param): static;

    public function getParameters(): array;

    public function toArray(): array;

    public function getSql(): string;
}
