<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Mysql;

use Blrf\Dbal\Config;
use Blrf\Dbal\Result;
use Blrf\Dbal\ResultStream;
use Blrf\Dbal\Driver\Connection as DriverConnection;
/* react/mysql:0.7.x
use React\Mysql\MysqlClient;
use React\Mysql\MysqlResult;
*/
use React\MySQL\Factory;
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
        /* react/mysql:0.7.x
        return resolve($this->setNativeConnection(new MysqlClient($this->config->getUri())));
        */
        return resolve($this->setNativeConnection((new Factory())->createLazyConnection($this->config->getUri())));
    }

    public function query(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Execute sql query
     *
     * @param array<int, string> $params
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface
    {
        // @phpstan-ignore-next-line
        return $this->getNativeConnection()->query($sql, $params)->then(
            function (/*MysqlResult */$res) {
                return new Result(
                    $res->resultRows ?? [],
                    $res->insertId,
                    $res->affectedRows ?? 0,
                    $res->warningCount ?? 0
                );
            }
        );
    }

    public function stream(string $sql, array $params = []): ResultStream
    {
        // @phpstan-ignore-next-line
        return new ResultStream($this->getNativeConnection()->queryStream($sql, $params));
    }

    public function quit(): PromiseInterface
    {
        // @phpstan-ignore-next-line
        return $this->getNativeConnection()->quit();
    }
}
