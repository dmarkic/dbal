<?php

declare(strict_types=1);

namespace Blrf\Dbal\Driver\Mysql;

use Blrf\Dbal\Config;
use Blrf\Dbal\Driver as DriverInterface;
use React\Promise\PromiseInterface;
use SensitiveParameter;

class Driver implements DriverInterface
{
    /**
     * Connect
     *
     * @return PromiseInterface<Connection>
     */
    public function connect(
        #[SensitiveParameter]
        Config $config
    ): PromiseInterface {
        return (new Connection($config))->connect();
    }
}
