<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver;

use Blrf\Dbal\Connection as ConnectionInterface;
use Blrf\Dbal\Config;
use React\Promise\PromiseInterface;

use function React\Promise\resolve;

abstract class Connection implements ConnectionInterface
{
    /**
     * Native connection
     */
    protected mixed $connection;

    public function __construct(public readonly Config $config)
    {
    }

    public function getDatabase(): ?string
    {
        return $this->config->getDatabase();
    }
    /**
     * Connect
     *
     * @return PromiseInterface<ConnectionInterface>
     */
    abstract public function connect(): PromiseInterface;

    /**
     * Start query builder
     *
     * @return QueryBuilder
     */
    abstract public function query(): QueryBuilder;

    /**
     * Execute query on connection and return Result
     * @return PromiseInterface<Result>
     */
    abstract public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Set underlying native connection
     */
    protected function setNativeConnection(mixed $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Retreive native connection
     */
    public function getNativeConnection(): mixed
    {
        return $this->connection;
    }
}
