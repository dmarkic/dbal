<?php

namespace Blrf\Tests\Dbal\Driver\Mysql;

use Blrf\Dbal\Config;
use Blrf\Dbal\Connection;
use Blrf\Dbal\Driver\Mysql\Driver;
use Blrf\Tests\Dbal\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function React\Async\await;
use function React\Promise\resolve;

#[CoversClass(Driver::class)]
class DriverTest extends TestCase
{
    /**
     * Nothing to test here really.
     */
    public function testConnect()
    {
        $driver = new Driver();
        $ret = $driver->connect(new Config());
        $this->assertInstanceOf(\React\Promise\PromiseInterface::class, $ret);
    }
}
