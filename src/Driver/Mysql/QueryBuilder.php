<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Mysql;

use Blrf\Dbal\Driver\QueryBuilder as DriverQueryBuilder;
use React\Promise\PromiseInterface;

class QueryBuilder extends DriverQueryBuilder
{
    /**
     * Execute query
     *
     * @return PromiseInterface<Result>
     */
    public function execute(): PromiseInterface
    {
        return $this->connection->execute($this->getSql(), $this->getParameters());
    }
}
