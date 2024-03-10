<?php

namespace Blrf\Tests\Dbal\Driver\Mysql;

use Blrf\Dbal\Connection;
use Blrf\Dbal\Driver\QueryBuilder as DriverQueryBuilder;
use Blrf\Dbal\Driver\Mysql\QueryBuilder;
use Blrf\Tests\Dbal\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function React\Async\await;
use function React\Promise\resolve;

#[CoversClass(QueryBuilder::class)]
#[CoversClass(DriverQueryBuilder::class)]
class QueryBuilderTest extends TestCase
{
    public function testExecute()
    {
        $sql = 'SELECT 1 + 1';
        $params = ['param' => true];
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('execute')->with($sql, $params)->willReturn(resolve('res'));
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$connection])
            ->onlyMethods(['getSql', 'getParameters'])
            ->getMock();
        $qb->method('getSql')->willReturn($sql);
        $qb->method('getParameters')->willReturn($params);
        $ret = await($qb->execute());
        $this->assertSame('res', $ret);
    }
}
