<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use React\Promise\PromiseInterface;
use SensitiveParameter;

/**
 * Driver interface
 */
interface Driver
{
    /**
     * Connect
     *
     * @return PromiseInterface<Connection>
     */
    public function connect(
        #[SensitiveParameter]
        Config $config
    ): PromiseInterface;
}
