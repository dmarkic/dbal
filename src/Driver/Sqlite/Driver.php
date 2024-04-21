<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Sqlite;

use Blrf\Dbal\Config;
use Blrf\Dbal\Driver as DriverInterface;
use Blrf\Dbal\Connection as ConnectionInterface;
use React\Promise\PromiseInterface;
use SensitiveParameter;

class Driver implements DriverInterface
{
    /**
     * Connect
     *
     * @return PromiseInterface<ConnectionInterface>
     */
    public function connect(
        #[SensitiveParameter]
        Config $config
    ): PromiseInterface {
        return (new Connection($config))->connect();
    }
}
