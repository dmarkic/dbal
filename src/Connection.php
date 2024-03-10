<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use React\Promise\PromiseInterface;

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
    public function query(): QueryBuilderInterface;

    /**
     * Execute query on connection
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Get native connection
     */
    public function getNativeConnection(): mixed;
}
