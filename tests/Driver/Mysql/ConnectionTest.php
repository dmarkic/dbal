<?php

namespace Blrf\Tests\Dbal\Driver\Mysql;

use Blrf\Dbal\Config;
use Blrf\Dbal\Driver\Connection as DriverConnection;
use Blrf\Dbal\Driver\Mysql\Connection;
use Blrf\Dbal\Driver\Mysql\QueryBuilder;
use Blrf\Tests\Dbal\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use React\Mysql\Io\Connection as MysqlConnection;
use React\Mysql\MysqlResult;

use function React\Async\await;
use function React\Promise\resolve;

#[CoversClass(Connection::class)]
#[CoversClass(DriverConnection::class)]
class ConnectionTest extends TestCase
{
    public function testConnection()
    {
        $config = new Config('mysql://localhost/database');
        $connection = new Connection($config);
        await($connection->connect());
        $this->assertNotNull($connection->getNativeConnection());
        $this->assertEquals('database', $connection->getDatabase());
        $this->assertInstanceOf(QueryBuilder::class, $connection->query());
    }

    public function testExecute()
    {
        $result = new MysqlResult();
        $result->resultRows = ['row'];
        $result->insertId = 2;
        $result->affectedRows = 3;
        $result->warningCount = 4;

        $sql = 'SELECT 1+1';
        $params = ['param'];

        $config = new Config();

        $mysqlConnection = $this->createMock(MysqlConnection::class);
        $mysqlConnection->expects($this->once())->method('query')->with($sql, $params)->willReturn(resolve($result));
        $connection = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([$config])
            ->onlyMethods(['getNativeConnection'])
            ->getMock();
        $connection->method('getNativeConnection')->willReturn($mysqlConnection);
        $ret = await($connection->execute($sql, $params));
        $this->assertSame($ret->rows, $result->resultRows);
        $this->assertSame($ret->insertId, $result->insertId);
        $this->assertSame($ret->affectedRows, $result->affectedRows);
        $this->assertSame($ret->warningCount, $result->warningCount);
    }
    /*
    public function testConnect()
    {
        $config = new Config('mysql://localhost/database');
        $connection = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([$config])
            ->onlyMethods(['setNativeConnection'])
            ->getMock();
        $connection->expects($this->once())->method('setNativeConnection')->with($this->anything());
        $connection->connect();
    }

    public function testQuery()
    {
        $config = new Config('mysql://localhost/database');
        $connection = new Connection($config);
        $qb = $connection->query();
    }
    */

}