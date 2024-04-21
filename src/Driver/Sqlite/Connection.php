<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Sqlite;

use Blrf\Dbal\Config;
use Blrf\Dbal\Result;
use Blrf\Dbal\ResultStream;
use Blrf\Dbal\Driver\Connection as DriverConnection;
use Clue\React\SQLite\Factory;
use Clue\React\SQLite\DatabaseInterface;
use Clue\React\SQLite\Result as SqliteResult;
use React\Promise\PromiseInterface;

use function React\Promise\resolve;

/**
 * React-Php Sqlite driver
 *
 * @see https://github.com/clue/reactphp-sqlite
 */
class Connection extends DriverConnection
{
    public function connect(): PromiseInterface
    {
        $factory = new Factory();
        return $factory->open('/' . $this->config->getDb(), SQLITE3_OPEN_READWRITE)->then(
            function (DatabaseInterface $db) {
                return $this->setNativeConnection($db);
            }
        );
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
            function (SqliteResult $res) {
                return new Result(
                    $res->rows ?? [],
                    $res->insertId,
                    $res->changed ?? 0,
                    0
                );
            }
        );
    }

    public function stream(string $sql, array $params = []): ResultStream
    {
        throw new \Exception('Stream not yet supported on sqlite');
    }

    public function quit(): PromiseInterface
    {
        return $this->getNativeConnection()->quit();
    }
}
