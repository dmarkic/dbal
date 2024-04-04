<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Mysql;

use Blrf\Dbal\Result;
use Blrf\Dbal\ResultStream;
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

    public function stream(): ResultStream
    {
        return $this->connection->stream($this->getSql(), $this->getParameters());
    }
}
