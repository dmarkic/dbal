<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use React\Promise\PromiseInterface;
use Blrf\Dbal\ResultStream;
use Blrf\Dbal\Driver\QueryBuilder;

/**
 * Connection interface
 */
interface Connection
{
    /**
     * Connect
     *
     * Connect to database and setNativeConnection().
     *
     * @return PromiseInterface<Connection>
     */
    public function connect(): PromiseInterface;

    /**
     * Start query builder
     *
     */
    public function query(): QueryBuilder;

    /**
     * Execute query on connection
     * @param mixed[] $params
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Execute query and return stream
     *
     * @param mixed[] $params
     */
    public function stream(string $sql, array $params = []): ResultStream;

    /**
     * Quit (soft-close) the connection
     *
     * @return PromiseInterface<void>
     */
    public function quit(): PromiseInterface;

    /**
     * Get native connection
     */
    public function getNativeConnection(): mixed;
}
