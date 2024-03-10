<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Mysql;

use Blrf\Dbal\Config;
use Blrf\Dbal\Result;
use Blrf\Dbal\Driver\Connection as DriverConnection;
use React\Mysql\MysqlClient;
use React\Mysql\MysqlResult;
use React\Promise\PromiseInterface;

use function React\Promise\resolve;

/**
 * React-Php MySQL driver
 *
 * @see https://github.com/friends-of-reactphp/mysql
 */
class Connection extends DriverConnection
{
    public function connect(): PromiseInterface
    {
        return resolve($this->setNativeConnection(new MysqlClient($this->config->getUri())));
    }

    public function query(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Execute sql query
     *
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface
    {
        return $this->getNativeConnection()->query($sql, $params)->then(
            function (MysqlResult $res) {
                return new Result(
                    $res->resultRows ?? [],
                    $res->insertId,
                    $res->affectedRows ?? 0,
                    $res->warningCount ?? 0
                );
            }
        );
    }
}
