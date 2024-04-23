<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\FromExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FromExpression::class)]
class FromExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new FromExpression('');
    }

    public function testConstructWithQueryBuilderAndEmptyAlias(): void
    {
        $this->expectException(\ValueError::class);
        new FromExpression(new QueryBuilder());
    }

    public function testToStringWithoutAlias(): void
    {
        $e = new FromExpression('from');
        $this->assertSame('from', (string)$e);
    }

    public function testToStringWithAlias(): void
    {
        $e = new FromExpression('from', 'alias');
        $this->assertSame('from AS alias', (string)$e);
    }

    public function testToStringWithSubquery(): void
    {
        $e = new FromExpression(new QueryBuilder(), 't1');
        $this->assertSame('(SELECT ) AS t1', (string)$e);
    }

    public function testToArray(): void
    {
        $e = new FromExpression('from', 'alias');
        $exp = [
            'expression'    => 'from',
            'alias'         => 'alias'
        ];
        $this->assertSame($exp, $e->toArray());
    }

    public function testToArrayWithQueryBuilder(): void
    {
        $e = new FromExpression(new QueryBuilder(), 'alias');
        $exp = [
            'expression'    => [
                'type'          => 'SELECT',
                'select'        => [],
                'from'          => [],
                'join'          => [],
                'columns'       => [],
                'where'         => null,
                'orderBy'       => [],
                'limit'         => null,
                'parameters'    =>  [],
            ],
            'alias'         => 'alias'
        ];
        $this->assertSame($exp, $e->toArray());
    }
}
