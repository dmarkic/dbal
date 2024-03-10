<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver;

use Blrf\Dbal\Connection as ConnectionInterface;
use Blrf\Dbal\QueryBuilder as BaseQueryBuilder;
use React\Promise\PromiseInterface;

/**
 * Query builder with connection and execute method.
 */
abstract class QueryBuilder extends BaseQueryBuilder
{
    public function __construct(public readonly ConnectionInterface $connection)
    {
    }

    /**
     * Execute query
     *
     * @return PromiseInterface<Result>
     */
    abstract public function execute(): PromiseInterface;
}
